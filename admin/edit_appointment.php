<?php
session_start();
require_once '../includes/db.php';

// Verificare autentificare admin
if (!isset($_SESSION["admin_logged_in"])) {
  header("Location: login.php");
  exit;
}

// Verificare existență ID programare
if (!isset($_GET['id'])) {
  header("Location: dashboard.php");
  exit;
}

$id = $_GET['id'];
$result = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
$result->bind_param("i", $id);
$result->execute();
$data = $result->get_result()->fetch_assoc();

if (!$data) {
  echo "<div class='alert alert-danger'>Appointment not found.</div>";
  exit;
}

// Obținem sărbătorile legale din DB
$holidays = [];
$res = $conn->query("SELECT holiday_date FROM holidays");
while ($row = $res->fetch_assoc()) {
  $holidays[] = $row['holiday_date'];
}

$error = "";

// Procesare formular salvare modificări
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pet_name = trim($_POST['pet_name']);
  $owner_name = trim($_POST['owner_name']);
  $phone = trim($_POST['phone']);
  $appointment_date = trim($_POST['appointment_date']);
  $appointment_time = trim($_POST['appointment_time']);
  $reason = trim($_POST['reason']);

  if (!$appointment_time) {
    $error = "Invalid appointment time.";
  } else {
    $full_datetime = $appointment_date . ' ' . $appointment_time;
    $selected_timestamp = strtotime($full_datetime);
    $day_of_week = date('w', strtotime($appointment_date));
    $formatted_date = date('Y-m-d', strtotime($appointment_date));

    // Validări server-side
    if ($selected_timestamp < time()) {
      $error = "You cannot set an appointment in the past.";
    } elseif ($day_of_week == 0 || in_array($formatted_date, $holidays)) {
      $error = "The clinic is closed on Sundays and public holidays.";
    } else {
      $check = $conn->prepare("SELECT * FROM appointments WHERE appointment_date = ? AND id != ?");
      $check->bind_param("si", $full_datetime, $id);
      $check->execute();
      $check_result = $check->get_result();

      if ($check_result->num_rows > 0) {
        $error = "The selected time slot is already booked.";
      } else {
        $stmt = $conn->prepare("UPDATE appointments SET pet_name=?, owner_name=?, phone=?, appointment_date=?, reason=? WHERE id=?");
        $stmt->bind_param("sssssi", $pet_name, $owner_name, $phone, $full_datetime, $reason, $id);

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
  <title>Edit Appointment - VetCare</title>
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
    }
    .edit-box {
      background-color: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      max-width: 600px;
      width: 100%;
    }
    .alert-message {
      display: none;
    }
  </style>
</head>
<body>

<div class="edit-box">
  <h4 class="mb-4 text-center">Edit Appointment</h4>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger" role="alert" id="serverError">
      <?= $error ?>
    </div>
  <?php endif; ?>

  <div id="clientError" class="alert alert-danger alert-message"></div>

  <form method="POST">
    <div class="mb-3">
      <label>Pet Name</label>
      <input type="text" name="pet_name" class="form-control" value="<?= htmlspecialchars($data['pet_name']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Owner Name</label>
      <input type="text" name="owner_name" class="form-control" value="<?= htmlspecialchars($data['owner_name']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Phone</label>
      <input type="tel" name="phone" class="form-control" pattern="^\+?\d{10,15}$" value="<?= htmlspecialchars($data['phone']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Date</label>
      <input type="date" name="appointment_date" id="appointment_date" class="form-control" value="<?= date('Y-m-d', strtotime($data['appointment_date'])) ?>" required>
    </div>
    <div class="mb-3">
      <label>Service</label>
      <select name="reason" id="reason" class="form-select" required>
        <?php
          $services = ["Consultation", "Vaccination", "Surgery", "Grooming", "Others"];
          foreach ($services as $service):
            $selected = ($data['reason'] === $service) ? 'selected' : '';
            echo "<option value='$service' $selected>$service</option>";
          endforeach;
        ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Available Time</label>
      <select name="appointment_time" id="appointment_time" class="form-select" required>
        <option value="">Select Time</option>
      </select>
    </div>
    <div class="text-center">
      <button type="submit" class="btn" style="background-color:#5cb85c; color:white; font-weight:500; padding: 8px 20px; border-radius: 30px;">Save Changes</button>
      <a href="dashboard.php" class="btn" style="background-color:#dc3545; color:white; font-weight:500; padding: 8px 20px; border-radius: 30px; margin-left: 10px;">Cancel</a>
    </div>
  </form>
</div>

<script>
  // Injectăm sărbătorile legale din PHP în JS
  const holidays = <?= json_encode($holidays) ?>;

  document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('appointment_date');
    const reasonSelect = document.getElementById('reason');
    const timeSelect = document.getElementById('appointment_time');
    const errorDiv = document.getElementById('clientError');
    const currentId = <?= $id ?>;
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);

    function getServiceDuration(service) {
      switch (service) {
        case 'Consultation': return 30;
        case 'Vaccination': return 20;
        case 'Surgery': return 120;
        case 'Grooming': return 45;
        case 'Others': return 30;
        default: return 0;
      }
    }

    function resetTimeOptions() {
      timeSelect.innerHTML = '<option value="">Select Time</option>';
    }

    function isDateValid(date) {
      const selected = new Date(date);
      const day = selected.getDay();
      return day !== 0 && !holidays.includes(date);
    }

    function showClientError(message) {
      errorDiv.textContent = message;
      errorDiv.style.display = 'block';
    }

    function fetchAvailableTimes() {
      const date = dateInput.value;
      const service = reasonSelect.value;
      const duration = getServiceDuration(service);

      if (!date || !service || !isDateValid(date)) {
        resetTimeOptions();
        return;
      }

      fetch(`../includes/get_available_times.php?date=${date}&duration=${duration}&exclude_id=${currentId}`)
        .then(response => response.json())
        .then(data => {
          resetTimeOptions();
          if (data.length === 0) {
            const option = document.createElement('option');
            option.textContent = 'No available slots';
            option.disabled = true;
            timeSelect.appendChild(option);
            return;
          }
          data.forEach(time => {
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time;
            if (time === "<?= date('H:i', strtotime($data['appointment_date'])) ?>") {
              option.selected = true;
            }
            timeSelect.appendChild(option);
          });
        })
        .catch(error => {
          console.error('Error:', error);
        });
    }

    dateInput.addEventListener('change', function () {
      if (!isDateValid(dateInput.value)) {
        showClientError("The clinic is closed on Sundays and public holidays. Please choose another day.");
        dateInput.value = "";
        resetTimeOptions();
      } else {
        errorDiv.style.display = 'none';
        fetchAvailableTimes();
      }
    });

    reasonSelect.addEventListener('change', fetchAvailableTimes);

    // Preîncărcăm orele disponibile dacă există deja o dată selectată
    if (dateInput.value && reasonSelect.value) {
      fetchAvailableTimes();
    }
  });
</script>

</body>
</html>
