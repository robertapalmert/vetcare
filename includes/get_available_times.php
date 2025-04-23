<?php 
require_once 'db.php'; // Conectarea la baza de date 

if (!isset($_GET['date']) || !isset($_GET['duration'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$duration = (int)$_GET['duration'];

// Program clinică
$weekday = date('N', strtotime($date)); // 1 = luni, 7 = duminică
if ($weekday == 7) { echo json_encode([]); exit; }
$start_hour = $weekday == 6 ? 10 : 8;
$end_hour = $weekday == 6 ? 14 : 17;

// Sărbători legale
$stmt = $conn->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    echo json_encode([]);
    exit;
}

// Obține toate programările din ziua respectivă
$stmt = $conn->prepare("SELECT appointment_date, reason FROM appointments WHERE DATE(appointment_date) = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$booked_intervals = [];
while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['appointment_date']);
    $dur = $duration;
    $end = clone $start;
    $end->modify("+{$dur} minutes");
    $booked_intervals[] = ['start' => $start, 'end' => $end];
}

// Generează toate sloturile posibile din zi
$available_slots = [];
$start_time = new DateTime("$date $start_hour:00");
$end_time = new DateTime("$date $end_hour:00");

while ($start_time < $end_time) {
    $slot_start = clone $start_time;
    $slot_end = clone $slot_start;
    $slot_end->modify("+{$duration} minutes");

    if ($slot_end > $end_time) break;

    // Verifică suprapunere
    $overlap = false;
    foreach ($booked_intervals as $interval) {
        if ($slot_start < $interval['end'] && $slot_end > $interval['start']) {
            $overlap = true;
            break;
        }
    }

    if (!$overlap) {
        $available_slots[] = $slot_start->format('H:i');
    }

    $start_time->modify("+15 minutes"); // pas fix
}

echo json_encode($available_slots);
?>