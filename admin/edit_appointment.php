<?php
session_start();
require_once '../includes/db.php';

// Redirecționare dacă nu e logat sau lipsește ID-ul
if (!isset($_SESSION["admin_logged_in"])) {
  header("Location: login.php");
  exit;
}

if (!isset($_GET['id'])) {
  header("Location: dashboard.php");
  exit;
}

$id = $_GET['id'];

// Obține programarea curentă
$stmt = $conn->prepare("
  SELECT a.*, s.name AS service_name 
  FROM appointments a
  JOIN services s ON a.service_id = s.id 
  WHERE a.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();
if (!$appointment) {
  echo "<div class='alert alert-danger m-4'>Appointment not found.</div>";
  exit;
}

// Servicii disponibile
$services = [];
$result = $conn->query("SELECT id, name, duration_minutes FROM services ORDER BY name");
while ($row = $result->fetch_assoc()) {
  $services[] = $row;
}

// Zile libere
$holidays = [];
$res = $conn->query("SELECT holiday_date FROM holidays");
while ($row = $res->fetch_assoc()) {
  $holidays[] = $row['holiday_date'];
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pet_name = trim($_POST['pet_name']);
  $owner_name = trim($_POST['owner_name']);
  $phone = trim($_POST['phone']);
  $appointment_date = trim($_POST['appointment_date']);
  $appointment_time = trim($_POST['appointment_time']);
  $service_id = intval($_POST['service_id']);


  if (!$appointment_time) {
    $error = "Invalid appointment time.";
  } else {
    $full_datetime = $appointment_date . ' ' . $appointment_time;
    $selected_timestamp = strtotime($full_datetime);
    $day_of_week = date('w', strtotime($appointment_date));
    $formatted_date = date('Y-m-d', strtotime($appointment_date));

    // Validări backend
    if ($selected_timestamp < time()) {
      $error = "You cannot book an appointment in the past.";
    } elseif ($day_of_week == 0 || in_array($formatted_date, $holidays)) {
      $error = "The clinic is closed on Sundays and public holidays.";
    } else {
      // Slot deja ocupat?
      $check = $conn->prepare("SELECT * FROM appointments WHERE appointment_date = ? AND id != ?");
      $check->bind_param("si", $full_datetime, $id);
      $check->execute();
      $check_result = $check->get_result();

      if ($check_result->num_rows > 0) {
        $error = "The selected time slot is already booked.";
      } else {
        // Salvare modificări
        $stmt = $conn->prepare("UPDATE appointments SET pet_name=?, owner_name=?, phone=?, appointment_date=?, service_id=? WHERE id=?");
        $stmt->bind_param("ssssii", $pet_name, $owner_name, $phone, $full_datetime, $service_id, $id);

        if ($stmt->execute()) {
          header("Location: dashboard.php?updated=1");
          exit;
        } else {
          $error = "Failed to update appointment.";
        }
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Appointment - VetCare</title>
  <link rel="icon" type="image/png" href="/vetcare_project/assets/images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to bottom right, #fffdf5, #f3e0c7);
      font-family: 'Quicksand', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .form-box {
      background-color: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      max-width: 600px;
      width: 100%;
    }
    .form-control:focus, .form-select:focus {
      border-color: #d4a75a !important;
      box-shadow: 0 0 0 0.2rem rgba(212, 167, 90, 0.25);
    }
    .btn-save {
      background-color: #5cb85c;
      color: white;
      font-weight: 500;
      padding: 8px 20px;
      border-radius: 30px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    .btn-save:hover {
      background-color: #4cae4c;
      color: white;
    }
  </style>
</head>
<body>

<div class="form-box">
  <h4 class="mb-4 text-center">Edit Appointment</h4>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <div id="validation-message"></div>

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
      <label>Date</label>
      <input type="date" name="appointment_date" id="appointment_date" class="form-control" value="<?= date('Y-m-d', strtotime($appointment['appointment_date'])) ?>" required>
    </div>
    <div class="mb-3">
      <label>Service</label>
      <select name="service_id" id="service_id" class="form-select" required>
        <option value="">Select a service</option>
        <?php foreach ($services as $service): ?>
          <option value="<?= $service['id'] ?>"
            data-duration="<?= $service['duration_minutes'] ?>"
            <?= $appointment['service_id'] == $service['id'] ? 'selected' : '' ?>>
            <?= $service['name'] ?> (<?= $service['duration_minutes'] ?> min)
          </option>
        <?php endforeach; ?>
      </select>

    </div>
    <div class="mb-3">
      <label>Available Time</label>
      <select name="appointment_time" id="appointment_time" class="form-select" required>
        <option value="">Select Time</option>
      </select>
    </div>
    <div class="text-center">
      <button type="submit" class="btn btn-save">Save Changes</button>
      <a href="dashboard.php" class="btn" style="background-color: #d4a75a; color: white; font-weight: 500; padding: 8px 20px; border-radius: 30px; margin-left: 10px;">Cancel</a>
    </div>
  </form>
</div>

<script>
  // Lista sărbătorilor legale preluată din PHP
  let holidays = <?= json_encode($holidays) ?>;

  // Afișează un mesaj de eroare în containerul dedicat
  function showValidationMessage(message) {
    document.getElementById("validation-message").innerHTML =
      `<div class="alert alert-danger mt-3" style="border-radius: 15px; font-weight: 500;">❗ ${message}</div>`;
  }

  document.addEventListener("DOMContentLoaded", function () {
    const reasonSelect = document.getElementById("service_id");
    const dateInput = document.getElementById("appointment_date");
    const timeSelect = document.getElementById("appointment_time");

    const today = new Date().toISOString().split("T")[0];
    dateInput.setAttribute("min", today); // Nu permite date anterioare

    const currentTime = "<?= date('H:i', strtotime($appointment['appointment_date'])) ?>";
    const currentId = <?= $id ?>;

    function resetTimeOptions() {
      timeSelect.innerHTML = '<option value="">Select Time</option>';
    }

    // Verifică dacă data este validă (nu e duminică și nu e zi liberă)
    function isDateValid(date) {
      const selected = new Date(date);
      const day = selected.getDay();
      return day !== 0 && !holidays.includes(date);
    }

    // Încarcă din backend sloturile disponibile pentru data și serviciul selectat
    function fetchAvailableTimes() {
      const date = dateInput.value;
      const selectedOption = reasonSelect.options[reasonSelect.selectedIndex];
      const duration = selectedOption ? parseInt(selectedOption.getAttribute("data-duration")) : 0;

      if (!date || duration <= 0 || !isDateValid(date)) {
        resetTimeOptions();
        return;
      }

      fetch(`../includes/get_available_times.php?date=${date}&duration=${duration}&exclude_id=${currentId}`)
        .then((res) => res.json())
        .then((data) => {
          resetTimeOptions();

          if (data.length === 0) {
            const option = document.createElement("option");
            option.textContent = "No available slots";
            option.disabled = true;
            timeSelect.appendChild(option);
            return;
          }

          data.forEach((time) => {
            const option = document.createElement("option");
            option.value = time;
            option.textContent = time;
            if (time === currentTime) option.selected = true;
            timeSelect.appendChild(option);
          });
        });
    }

    // Validare când se schimbă data
    dateInput.addEventListener("change", function () {
      if (!isDateValid(dateInput.value)) {
        showValidationMessage("The clinic is closed on Sundays and public holidays. Please choose another day.");
        dateInput.value = "";
        resetTimeOptions();
      } else {
        document.getElementById("validation-message").innerHTML = "";
        fetchAvailableTimes();
      }
    });

    // Când se schimbă serviciul, se reîncarcă orele (dacă data e validă)
    reasonSelect.addEventListener("change", function () {
      if (dateInput.value && isDateValid(dateInput.value)) {
        fetchAvailableTimes();
      }
    });

    // Dacă există date precompletate, încărcăm orele
    if (dateInput.value && reasonSelect.value) {
      fetchAvailableTimes();
    }

    // Previne trimiterea formularului dacă ora selectată este în trecut
    document.querySelector("form").addEventListener("submit", function (e) {
      const dateVal = dateInput.value;
      const timeVal = timeSelect.value;

      if (!dateVal || !timeVal) return;

      const selectedDateTime = new Date(dateVal + "T" + timeVal);
      const now = new Date();

      if (selectedDateTime < now) {
        e.preventDefault();
        showValidationMessage("You cannot book an appointment in the past.");
      }
    });
  });
</script>

</body>
</html>
