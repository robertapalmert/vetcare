<?php 
require_once __DIR__ . '/db.php'; // Conectare la baza de date

// Verificăm dacă au fost trimise parametrii necesari
if (!isset($_GET['date']) || !isset($_GET['duration'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$duration = (int)$_GET['duration'];

// Stabilim programul clinicii în funcție de zi
$weekday = date('N', strtotime($date)); // 1 = luni, 7 = duminică
if ($weekday == 7) { // Duminica e închis
    echo json_encode([]);
    exit;
}
$start_hour = $weekday == 6 ? 10 : 8;
$end_hour   = $weekday == 6 ? 14 : 17;

// Verificăm dacă data este o sărbătoare legală
$stmt = $conn->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) { // Dacă e sărbătoare legală, nu sunt disponibile programări
    echo json_encode([]);
    exit;
}

// Obținem toate programările existente pentru ziua respectivă
$stmt = $conn->prepare("SELECT appointment_date, reason FROM appointments WHERE DATE(appointment_date) = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$booked_intervals = [];
while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['appointment_date']);
    $dur   = $duration;
    $end   = clone $start;
    $end->modify("+{$dur} minutes");
    $booked_intervals[] = ['start' => $start, 'end' => $end];
}

// Generăm toate intervalele posibile în ziua respectivă
$available_slots = [];
$start_time = new DateTime("$date $start_hour:00");
$end_time   = new DateTime("$date $end_hour:00");

while ($start_time < $end_time) {
    $slot_start = clone $start_time;
    $slot_end   = clone $slot_start;
    $slot_end->modify("+{$duration} minutes");

    if ($slot_end > $end_time) break;

    // Verificăm dacă există suprapuneri cu alte programări
    $overlap = false;
    foreach ($booked_intervals as $interval) {
        if ($slot_start < $interval['end'] && $slot_end > $interval['start']) {
            $overlap = true;
            break;
        }
    }

    // Dacă nu se suprapune, adăugăm intervalul la cele disponibile
    if (!$overlap) {
        $available_slots[] = $slot_start->format('H:i');
    }

    // Trecem la următorul interval (cu pas de 15 minute)
    $start_time->modify("+15 minutes");
}

// Returnăm intervalele disponibile în format JSON
echo json_encode($available_slots);
?>
