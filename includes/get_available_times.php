<?php
include 'db.php'; // conectarea la baza de date 

if (!isset($_GET['date']) || !isset($_GET['reason'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$reason = $_GET['reason'];

// Duratele serviciilor
$durations = [
    'Consultation' => 30,
    'Vaccination' => 20,
    'Surgery' => 120,
    'Grooming' => 45,
    'Others' => 30
];
$duration = isset($durations[$reason]) ? $durations[$reason] : 30;

// Program clinică
$weekday = date('N', strtotime($date)); // 1 = luni, 7 = duminică
if ($weekday == 7) {
    echo json_encode([]);
    exit;
} elseif ($weekday == 6) {
    $start_hour = 10;
    $end_hour = 14;
} else {
    $start_hour = 8;
    $end_hour = 17;
}

// Obține programările existente
include 'db.php';
$stmt = $conn->prepare("SELECT appointment_date FROM appointments WHERE DATE(appointment_date) = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$booked_slots = [];
while ($row = $result->fetch_assoc()) {
    $booked_slots[] = date('H:i', strtotime($row['appointment_date']));
}

// Generează orele disponibile
$available_slots = [];
$start_time = new DateTime("$date $start_hour:00");
$end_time = new DateTime("$date $end_hour:00");

while ($start_time <= $end_time) {
    $slot_start = clone $start_time;
    $slot_end = clone $start_time;
    $slot_end->add(new DateInterval("PT{$duration}M"));

    // Ignoră dacă depășește programul clinicii
    if ($slot_end > $end_time) break;

    // Verifică suprapunere cu sloturi ocupate
    $overlap = false;
    foreach ($booked_slots as $booked) {
        $booked_start = new DateTime("$date $booked");
        $booked_end = clone $booked_start;
        $booked_end->add(new DateInterval("PT{$duration}M"));

        if (
            ($slot_start < $booked_end) &&
            ($slot_end > $booked_start)
        ) {
            $overlap = true;
            break;
        }
    }

    if (!$overlap) {
        $available_slots[] = $slot_start->format('H:i');
    }

    $start_time->add(new DateInterval("PT{$duration}M"));
}

echo json_encode($available_slots);
?>