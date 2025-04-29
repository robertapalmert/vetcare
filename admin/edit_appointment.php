<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION["admin_logged_in"])) {
  header("Location: login.php");
  exit;
}

// Verificăm dacă avem ID valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Invalid appointment ID.";
  exit;
}

$id = $_GET['id'];

// Dacă s-a trimis formularul
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $pet_name = $_POST["pet_name"];
  $owner_name = $_POST["owner_name"];
  $phone = $_POST["phone"];
  $appointment_date = $_POST["appointment_date"];
  $appointment_time = $_POST["appointment_time"];
  $reason = $_POST["reason"];

  $appointment_datetime = $appointment_date . ' ' . $appointment_time;

  $stmt = $conn->prepare("UPDATE appointments SET pet_name=?, owner_name=?, phone=?, appointment_date=?, reason=? WHERE id=?");
  $stmt->bind_param("sssssi", $pet_name, $owner_name, $phone, $appointment_datetime, $reason, $id);

  if ($stmt->execute()) {
    header("Location: dashboard.php?updated=1");
    exit;
  } else {
    $error = "Error updating appointment: " . $stmt->error;
  }

  $stmt->close();
}

// Preluăm datele existente
$stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment) {
  echo "Appointment not found.";
  exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Appointment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body {
      background: #f8f9fa;
      font-family: 'Quicksand', sans-serif;
      padding-top: 40px;
    }
    .container {
      max-width: 600px;
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .btn-primary {
      background-color: #c89f68;
      border: none;
    }
    .btn-primary:hover {
      background-color: #dcb177;
    }
  </style>
</head>
<body>

<div class="container">
  <h3 class="text-center mb-4">Edit Appointment</h3>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger text-center"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label>Pet Name</label>
      <input type="text" name="pet_name" class="form-control" value="<?= htmlspecialchars($appointment['pet_name']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Owner Name</label>
      <input type="text" name="owner_name" class="form-control" value="<?= htmlspecialchars($appointment['owner_name']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Phone</label>
      <input type="tel" name="phone" class="form-control" pattern="^\+?\d{10,15}$" value="<?= htmlspecialchars($appointment['phone']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Reason</label>
      <select name="reason" class="form-select" required>
        <?php
        $reasons = ["Consultation", "Vaccination", "Surgery", "Grooming", "Others"];
        foreach ($reasons as $r) {
          $selected = ($appointment['reason'] === $r) ? 'selected' : '';
          echo "<option value='$r' $selected>$r</option>";
        }
        ?>
      </select>
    </div>

    <div class="mb-3">
      <label>Date</label>
      <input type="date" name="appointment_date" class="form-control"
             value="<?= explode(' ', $appointment['appointment_date'])[0] ?>" required>
    </div>

    <div class="mb-4">
      <label>Time</label>
      <input type="time" name="appointment_time" class="form-control"
             value="<?= explode(' ', $appointment['appointment_date'])[1] ?>" required>
    </div>

    <div class="d-flex justify-content-between">
      <a href="dashboard.php" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-primary">Update Appointment</button>
    </div>
  </form>
</div>

</body>
</html>
