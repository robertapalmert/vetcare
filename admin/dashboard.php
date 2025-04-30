<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION["admin_logged_in"])) {
  header("Location: login.php");
  exit;
}

// Preluăm statistici
$totalAppointments = $conn->query("SELECT COUNT(*) AS total FROM appointments")->fetch_assoc()['total'];

$todayDate = date('Y-m-d');
$appointmentsToday = $conn->query("SELECT COUNT(*) AS today FROM appointments WHERE DATE(appointment_date) = '$todayDate'")->fetch_assoc()['today'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - VetCare</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Quicksand', sans-serif;
      background: linear-gradient(to bottom right, #fffdf5, #f3e0c7);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      background-color: #c89f68;
      color: white;
      padding: 15px 0;
    }
    .nav-link {
      color: white !important;
      margin-left: 25px;
      font-weight: 500;
    }
    .dashboard {
      margin: 40px auto;
      width: 95%;
      max-width: 1300px;
    }
    .table {
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .table th {
      background-color: #c89f68;
      color: white;
      font-weight: 600;
      position: sticky;
      top: 0;
    }
    footer {
      background-color: #f8f9fa;
      text-align: center;
      padding: 15px 0;
      margin-top: auto;
    }
  </style>
</head>
<body>

<header>
  <div class="container d-flex justify-content-between align-items-center">
    <h2 class="mb-0">VetCare</h2>
    <nav>
      <a href="/vetcare_project/public/index.html" class="nav-link d-inline">Home</a>
      <a href="/vetcare_project/admin/logout.php" class="nav-link d-inline">Logout</a>
      <a href="/vetcare_project/admin/account_settings.php" class="nav-link d-inline">Account Settings</a>
    </nav>
  </div>
</header>

<div class="dashboard">

  <h3 class="text-center mb-4">All Appointments</h3>

  <!-- Search Box -->
  <div class="text-center mb-4">
    <input type="text" id="searchInput" onkeyup="filterTable()" class="form-control" placeholder="Search by Pet, Owner or Phone..." style="max-width: 400px; margin: 0 auto; border-radius: 30px; padding: 10px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
  </div>

  <!-- Statistici -->
  <div class="row mb-4 text-center">
    <div class="col-md-6">
      <div class="alert alert-info" style="border-radius: 15px; font-weight: 500;">
        📅 Appointments Today: <?= $appointmentsToday ?>
      </div>
    </div>
    <div class="col-md-6">
      <div class="alert alert-success" style="border-radius: 15px; font-weight: 500;">
        📋 Total Appointments: <?= $totalAppointments ?>
      </div>
    </div>
  </div>

  <!-- Mesaje Confirmare -->
  <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-success text-center" style="border-radius: 15px; font-weight: 500;">
      ✅ Appointment has been successfully cancelled.
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-info text-center" style="border-radius: 15px; font-weight: 500;">
      ✅ Appointment has been successfully updated.
    </div>
  <?php endif; ?>

  <!-- Formular Filtrare -->
  <form method="GET" class="mb-4 d-flex justify-content-center gap-3 flex-wrap">
    <input type="date" name="date" class="form-control" style="max-width: 200px;" value="<?= isset($_GET['date']) ? $_GET['date'] : '' ?>">

    <select name="service" class="form-select" style="max-width: 200px;">
      <option value="">All Services</option>
      <?php
      $services = ["Consultation", "Vaccination", "Surgery", "Grooming", "Others"];
      foreach ($services as $service) {
          $selected = (isset($_GET['service']) && $_GET['service'] == $service) ? 'selected' : '';
          echo "<option value='$service' $selected>$service</option>";
      }
      ?>
    </select>

    <button type="submit" class="btn" style="background-color: #c89f68; color: white; border-radius: 30px; padding: 8px 20px; font-weight: 500;">Filter</button>
    <a href="dashboard.php" class="btn" style="background-color: #5cb85c;
; color: white; border-radius: 30px; padding: 8px 20px; font-weight: 500;">Reset</a>
  </form>

  <!-- Tabel cu Scroll -->
  <div style="max-height: 500px; overflow-y: auto; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
    <table class="table table-bordered text-center mb-0">
      <thead>
        <tr>
          <th>ID</th>
          <th>Pet Name</th>
          <th>Owner</th>
          <th>Phone</th>
          <th>Date</th>
          <th>Reason</th>
          <th>Reminder</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Filtrare
        $query = "SELECT * FROM appointments";
        $conditions = [];

        if (isset($_GET['date']) && !empty($_GET['date'])) {
          $conditions[] = "DATE(appointment_date) = '" . $conn->real_escape_string($_GET['date']) . "'";
        }

        if (isset($_GET['service']) && !empty($_GET['service'])) {
          $conditions[] = "reason = '" . $conn->real_escape_string($_GET['service']) . "'";
        }

        if (!empty($conditions)) {
          $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY appointment_date ASC";

        $result = $conn->query($query);

        while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['pet_name']) ?></td>
          <td><?= htmlspecialchars($row['owner_name']) ?></td>
          <td><?= htmlspecialchars($row['phone']) ?></td>
          <td><?= $row['appointment_date'] ?></td>
          <td><?= htmlspecialchars($row['reason']) ?></td>
          <td>Reminder Scheduled</td>
          <td>
            <a href="edit_appointment.php?id=<?= $row['id'] ?>" class="btn btn-sm" 
               style="background-color: #c89f68; color: white; border-radius: 30px; padding: 6px 14px; font-weight: 500;">
              Edit
            </a>
            <a href="delete_appointment.php?id=<?= $row['id'] ?>" class="btn btn-sm"
               onclick="return confirm('Are you sure you want to cancel this appointment? It will be deleted permanently!');"
               style="background-color: #dc3545; color: white; border-radius: 30px; padding: 6px 14px; font-weight: 500;">
              Cancel
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>

<footer>
  <small>VetCare Clinic &copy; 2025 - All rights reserved</small>
</footer>

<!-- Script Search Box -->
<script>
function filterTable() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("searchInput");
  filter = input.value.toLowerCase();
  table = document.querySelector(".table tbody");
  tr = table.getElementsByTagName("tr");

  for (i = 0; i < tr.length; i++) {
    let match = false;
    const tds = tr[i].getElementsByTagName("td");

    for (let j = 1; j <= 3; j++) { // Pet Name, Owner Name, Phone
      if (tds[j]) {
        txtValue = tds[j].textContent || tds[j].innerText;
        if (txtValue.toLowerCase().indexOf(filter) > -1) {
          match = true;
          break;
        }
      }
    }

    tr[i].style.display = match ? "" : "none";
  }
}
</script>

</body>
</html>
