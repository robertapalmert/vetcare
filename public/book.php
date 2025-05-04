<?php
require_once '../includes/db.php';

// Obținem serviciile disponibile din baza de date
$services = [];
$result = $conn->query("SELECT id, name, duration_minutes FROM services ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

// Obținem sărbătorile legale
$holidays = [];
$res = $conn->query("SELECT holiday_date FROM holidays");
while ($row = $res->fetch_assoc()) {
    $holidays[] = $row['holiday_date'];
}

// Salvăm programarea dacă s-a trimis formularul
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pet_name = trim($_POST["pet_name"]);
    $owner_name = trim($_POST["owner_name"]);
    $phone = trim($_POST["phone"]);
    $appointment_date = trim($_POST["appointment_date"]);
    $appointment_time = trim($_POST["appointment_time"]);
    $service_id = intval($_POST["service_id"]);


    $full_datetime = $appointment_date . ' ' . $appointment_time;
    $selected_timestamp = strtotime($full_datetime);
    $day_of_week = date('w', strtotime($appointment_date));
    $formatted_date = date('Y-m-d', strtotime($appointment_date));

    // Verificări backend (la fel ca în edit_appointment.php)
    if ($selected_timestamp < time()) {
        $error = "You cannot book an appointment in the past.";
    } elseif ($day_of_week == 0 || in_array($formatted_date, $holidays)) {
        $error = "The clinic is closed on Sundays and public holidays.";
    } else {
        // Verificăm dacă slotul este deja ocupat
        $check = $conn->prepare("SELECT * FROM appointments WHERE appointment_date = ?");
        $check->bind_param("s", $full_datetime);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $error = "The selected time slot is already booked.";
        } else {
            // Inserăm programarea
            $stmt = $conn->prepare("INSERT INTO appointments (pet_name, owner_name, phone, appointment_date, service_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $pet_name, $owner_name, $phone, $full_datetime, $service_id);
            

            if ($stmt->execute()) {
                // Mesaj de succes + redirectare
                echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Booking Success</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>body{background:linear-gradient(to bottom right,#fffdf5,#f3e0c7);font-family:Quicksand,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.message-box{background:white;padding:40px;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.1);text-align:center;max-width:600px;width:90%}</style></head><body>
                <div class="message-box"><div class="alert alert-success" style="font-weight:500;">🎉 Appointment booked successfully! ✉️ A confirmation message has been sent to your phone number.</div>
                <p class="mt-4">You will be redirected to the homepage in 5 seconds...</p></div>
                <script>setTimeout(()=>{window.location.href="../public/index.html"},5000);</script></body></html>';
                exit();
            } else {
                $error = "Error booking appointment: " . htmlspecialchars($stmt->error);
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
  <title>Book Appointment - VetCare</title>
  <link rel="icon" href="/vetcare_project/assets/images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Quicksand', sans-serif;
      background: linear-gradient(to bottom right, #fffdf5, #f3e0c7);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header { background-color: #c89f68; color: white; padding: 15px 0; }
    .nav-link { color: white !important; margin-left: 25px; font-weight: 500; }
    .container form {
      background-color: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .form-group label { font-weight: 600; }
    .form-control:focus, .form-select:focus {
      border-color: #c89f68;
      box-shadow: 0 0 0 0.2rem rgba(200, 159, 104, 0.4);
    }
    .book-btn {
      background-color: #c89f68;
      color: white;
      border-radius: 30px;
      padding: 12px 30px;
      font-weight: 600;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      transition: all 0.3s ease-in-out;
      text-decoration: none;
    }
    .book-btn:hover {
      background-color: #dcb177;
      transform: translateY(-3px);
      box-shadow: 0 6px 14px rgba(0,0,0,0.25);
      color: white;
      text-decoration: none;
    }
    footer { background-color: #f8f9fa; text-align: center; padding: 15px 0; margin-top: auto; }

  .submit-btn {
  background-color: #c89f68;
  color: white;
  border-radius: 30px;
  padding: 12px 30px;
  font-weight: 600;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  transition: all 0.3s ease-in-out;
  text-decoration: none;
  border: none;
}

.submit-btn:hover {
  background-color: #dcb177;
  transform: translateY(-3px);
  box-shadow: 0 6px 14px rgba(0,0,0,0.25);
  color: black !important;
  text-decoration: none;
}

  </style>
</head>
<body>

<header>
  <div class="container d-flex justify-content-between align-items-center">
    <h2 class="mb-0 d-flex align-items-center">
      <a href="../public/index.html" class="d-flex align-items-center" style="text-decoration: none;">
        <img src="../assets/images/logo.png" alt="VetCare Logo" style="height: 36px; margin-right: 10px;">
        <span style="color: white; font-weight: bold;">VetCare</span>
      </a>
    </h2>
    <nav>
      <a href="/vetcare_project/public/index.html" class="nav-link d-inline">Home</a>
      <a href="book.php" class="nav-link d-inline">Book Appointment</a>
      <a href="/vetcare_project/admin/login.php" class="nav-link d-inline">Admin</a>
    </nav>
  </div>
</header>

<div class="container my-5">
  <h3 class="text-center mb-4">Book a Veterinary Appointment</h3>

  <form action="book.php" method="POST">
    <div class="form-group mb-3">
      <label for="pet_name">Pet Name</label>
      <input type="text" class="form-control" name="pet_name" id="pet_name" required />
    </div>

    <div class="form-group mb-3">
      <label for="owner_name">Owner Name</label>
      <input type="text" class="form-control" name="owner_name" id="owner_name" required />
    </div>

    <div class="form-group mb-3">
      <label for="phone">Phone</label>
      <input type="tel" class="form-control" name="phone" id="phone" pattern="^\+?\d{10,15}$" required />
    </div>

    <div class="form-group mb-3">
      <label for="service_id">Service</label>
      <select name="service_id" id="service_id" class="form-select" required>
        <option value="">Select a Service</option>
        <?php foreach ($services as $service): ?>
          <option value="<?php echo $service['id']; ?>" data-duration="<?php echo $service['duration_minutes']; ?>">
            <?= htmlspecialchars($service['name']) ?> (<?= $service['duration_minutes'] ?> min)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group mb-3">
      <label for="appointment_date">Appointment Date</label>
      <input type="date" class="form-control" name="appointment_date" id="appointment_date" required />
    </div>

    <div class="form-group mb-4">
      <label for="appointment_time">Appointment Time</label>
      <select name="appointment_time" id="appointment_time" class="form-select" required>
        <option value="">Select Time</option>
      </select>
    </div>

    <div class="text-center">
      <button type="submit" class="submit-btn">Submit</button>
    </div>
  </form>

  <div id="validation-message" class="text-center mt-4"></div>
</div>

<footer>
  <small>VetCare Clinic &copy; 2025 - All rights reserved</small>
</footer>

<script>
  // Sărbători legale primite de la server
  let holidays = [];

  // Afișează mesaj de eroare în formular
  function showValidationMessage(message) {
    document.getElementById("validation-message").innerHTML =
      `<div class="alert alert-danger mt-3" style="border-radius: 15px; font-weight: 500;">❗ ${message}</div>`;
  }

  document.addEventListener("DOMContentLoaded", function () {
    const reasonSelect = document.getElementById("service_id");
    const dateInput = document.getElementById("appointment_date");
    const timeSelect = document.getElementById("appointment_time");
    const today = new Date().toISOString().split("T")[0];
    dateInput.setAttribute("min", today);

    // Inițializare: preluăm sărbătorile legale
    fetch("../includes/get_available_times.php?mode=init")
      .then(res => res.json())
      .then(data => { holidays = data.holidays; });

    function resetTimeOptions() {
      timeSelect.innerHTML = '<option value="">Select Time</option>';
    }

    function isDateValid(date) {
      const selected = new Date(date);
      const day = selected.getDay();
      return day !== 0 && !holidays.includes(date);
    }

    function fetchAvailableTimes() {
      const date = dateInput.value;
      const selectedOption = reasonSelect.options[reasonSelect.selectedIndex];
      const duration = selectedOption ? parseInt(selectedOption.getAttribute("data-duration")) : 0;

      if (!date || duration <= 0 || !isDateValid(date)) {
        resetTimeOptions();
        return;
      }

      fetch(`../includes/get_available_times.php?date=${date}&duration=${duration}`)
        .then(res => res.json())
        .then(data => {
          resetTimeOptions();

          if (data.length === 0) {
            const option = document.createElement("option");
            option.textContent = "No available slots";
            option.disabled = true;
            timeSelect.appendChild(option);
            return;
          }

          data.forEach(time => {
            const option = document.createElement("option");
            option.value = time;
            option.textContent = time;
            timeSelect.appendChild(option);
          });
        });
    }

    // Când se selectează o dată
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

    // Când se selectează un serviciu
    reasonSelect.addEventListener("change", function () {
      if (dateInput.value && isDateValid(dateInput.value)) {
        fetchAvailableTimes();
      }
    });

    // Validare înainte de trimiterea formularului
    document.querySelector("form").addEventListener("submit", function (e) {
      const dateVal = dateInput.value;
      const timeVal = timeSelect.value;

      if (!dateVal || !timeVal) return;

      const selectedDateTime = new Date(dateVal + "T" + timeVal);
      if (selectedDateTime < new Date()) {
        e.preventDefault();
        showValidationMessage("You cannot book an appointment in the past.");
      }
    });
  });
</script>

</body>
</html>

