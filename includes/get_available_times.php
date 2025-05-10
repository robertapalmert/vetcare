<?php
require_once __DIR__ . '/db.php';

// Inițializare pentru frontend (servicii, program clinică, sărbători)
if (isset($_GET['mode']) && $_GET['mode'] === 'init') {
    $response = [];

    // Servicii disponibile
    $services = [];
    $result = $conn->query("SELECT name, duration_minutes FROM services ORDER BY name");
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    $response['services'] = $services;

    // Programul clinicii
    $working_hours = [];
    $res = $conn->query("SELECT day_of_week, open_time, close_time, is_open FROM working_hours");
    while ($row = $res->fetch_assoc()) {
        $working_hours[$row['day_of_week']] = [
            'open' => $row['open_time'],
            'close' => $row['close_time'],
            'is_open' => (bool) $row['is_open']
        ];
    }
    $response['working_hours'] = $working_hours;

    // Zile libere legale
    $holidays = [];
    $res = $conn->query("SELECT holiday_date FROM holidays");
    while ($row = $res->fetch_assoc()) {
        $holidays[] = $row['holiday_date'];
    }
    $response['holidays'] = $holidays;

    echo json_encode($response);
    exit;
}

// Validare parametri de interogare
if (!isset($_GET['date']) || !isset($_GET['duration'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'];
$duration = (int)$_GET['duration'];
$day_number = date('N', strtotime($date)); // 1 = luni, 7 = duminică

// Verificăm dacă clinica e deschisă în ziua respectivă 
$stmt = $conn->prepare("SELECT open_time, close_time, is_open FROM working_hours WHERE day_of_week = ?");
$stmt->bind_param("i", $day_number);
$stmt->execute();
$result = $stmt->get_result();
$working = $result->fetch_assoc();
$stmt->close();

if (!$working || !$working['is_open']) {
    echo json_encode([]);
    exit;
}

$start_hour = $working['open_time'];
$end_hour = $working['close_time'];

// Excludem sărbătorile legale 
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

// Obținem programările existente pentru acea zi 
$stmt = $conn->prepare("
    SELECT a.appointment_date, s.duration_minutes 
    FROM appointments a 
    JOIN services s ON a.service_id = s.id 
    WHERE DATE(a.appointment_date) = ?
");

$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$booked_intervals = [];
while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['appointment_date']);
    $end = clone $start;
    $end->modify("+{$row['duration_minutes']} minutes");
    $booked_intervals[] = ['start' => $start, 'end' => $end];
}

// Generăm sloturi disponibile 
$available_slots = [];
$start_time = new DateTime("$date $start_hour");
$end_time = new DateTime("$date $end_hour");

while ($start_time < $end_time) {
    $slot_start = clone $start_time;
    $slot_end = clone $slot_start;
    $slot_end->modify("+{$duration} minutes");

    if ($slot_end > $end_time) break;

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

    // Se trece la următorul slot (interval de 15 minute)
    $start_time->modify("+15 minutes");
}

// Răspuns final cu toate sloturile disponibile
echo json_encode($available_slots);
?>
