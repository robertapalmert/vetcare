<?php
require_once '../includes/db.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Preluare date din formular
    $pet_name = trim($_POST["pet_name"]);
    $owner_name = trim($_POST["owner_name"]);
    $phone = trim($_POST["phone"]);
    $appointment_date = trim($_POST["appointment_date"]);
    $appointment_time = trim($_POST["appointment_time"]);
    $reason = trim($_POST["reason"]);

    // Combinare dată + oră
    $full_datetime = $appointment_date . ' ' . $appointment_time;
    $selected_timestamp = strtotime($full_datetime);

    // Inserare în baza de date
    $stmt = $conn->prepare("INSERT INTO appointments (pet_name, owner_name, phone, appointment_date, reason) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $pet_name, $owner_name, $phone, $full_datetime, $reason);

    if ($stmt->execute()) {
        $successMessage = "🎉 Appointment booked successfully! ✉️ A confirmation message has been sent to your phone number.";
    } else {
        $errorMessage = "❌ Error booking appointment: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Booking Result - VetCare</title>
  <link rel="icon" type="image/png" href="/vetcare_project/assets/images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to bottom right, #fffdf5, #f3e0c7);
      font-family: 'Quicksand', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }
    .message-box {
      background: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 600px;
      width: 90%;
    }
  </style>
</head>
<body>

<!-- Afișare mesaj de succes sau eroare -->
<div class="message-box">
  <?php if (!empty($successMessage)): ?>
    <div class="alert alert-success" style="font-weight: 500;">
      <?= $successMessage ?>
    </div>
    <p class="mt-4">You will be redirected to the homepage in 5 seconds...</p>
    <script>
      setTimeout(function() {
        window.location.href = "../public/index.html"; 
      }, 5000);
    </script>
  <?php elseif (!empty($errorMessage)): ?>
    <div class="alert alert-danger" style="font-weight: 500;">
      <?= $errorMessage ?>
    </div>
    <div class="mt-4">
      <a href="../public/book.html" class="btn btn-warning">Back to Booking</a>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
