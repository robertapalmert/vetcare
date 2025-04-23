<?php
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_name = $_POST["pet_name"];
    $owner_name = $_POST["owner_name"];
    $phone = $_POST["phone"];
    $appointment_date = $_POST["appointment_date"];
    $appointment_time = $_POST["appointment_time"];
    $full_datetime = $appointment_date . ' ' . $appointment_time;
    // Verificare dacă data + ora sunt în trecut
    $selected_timestamp = strtotime($full_datetime);
    if ($selected_timestamp < time()) {
    echo "<script>alert('Nu poți face o programare în trecut.'); window.history.back();</script>";
    exit;
    }
    $reason = $_POST["reason"];

    $stmt = $conn->prepare("INSERT INTO appointments (pet_name, owner_name, phone, appointment_date, reason) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $pet_name, $owner_name, $phone, $full_datetime, $reason);

    if ($stmt->execute()) {
        echo "<script>alert('Appointment booked successfully!'); window.location.href = 'index.html';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
