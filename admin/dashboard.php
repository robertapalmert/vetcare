<?php
session_start();
require_once '../includes/db.php';

// Verificare autentificare admin
if (!isset($_SESSION["admin_logged_in"])) {
  header("Location: login.php");
  exit;
}

// Obținere statistici
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
  <link rel="icon" type="image/png" href="/vetcare_project/assets/images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

    .form-control:focus, .form-select:focus {
      border-color: #d4a75a !important;
      box-shadow: 0 0 0 0.2rem rgba(212, 167, 90, 0.25);
      outline: none;
    }
    
  </style>
</head>
<body>

<!-- Antetul paginii admin -->
<header>
  <div class="container d-flex justify-content-between align-items-center">
    <h2 class="mb-0 d-flex align-items-center">
      <a href="/vetcare_project/public/index.html" class="d-flex align-items-center" style="text-decoration: none;">
        <img src="/vetcare_project/assets/images/logo.png" alt="VetCare Logo" style="height: 36px; margin-right: 10px;">
        <span style="color: white; font-weight: bold;">VetCare</span>
      </a>
    </h2>
    <nav>
      <a href="/vetcare_project/public/index.html" class="nav-link d-inline">Home</a>
      <a href="/vetcare_project/admin/logout.php" class="nav-link d-inline">Logout</a>
      <a href="/vetcare_project/admin/account_settings.php" class="nav-link d-inline">Account Settings</a>
    </nav>
  </div>
</header>

<div class="dashboard">
  <h3 class="text-center mb-4">All Appointments</h3>

  <!-- Căsuță de căutare -->
  <div class="text-center mb-4">
    <input type="text" id="searchInput" onkeyup="filterTable()" class="form-control"
      placeholder="Search by Pet, Owner or Phone..."
      style="max-width: 400px; margin: 0 auto; border-radius: 30px; padding: 10px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
  </div>

  <!-- Statistici  -->
  <div class="d-flex justify-content-end mb-3 px-3" style="font-weight: 500;">
    <div class="me-4">📅 Today: <?= $appointmentsToday ?></div>
    <div>📋 Total: <?= $totalAppointments ?></div>
  </div>

  <!-- Afișare mesaje de confirmare -->
  <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="alert alert-success text-center" style="border-radius: 15px; font-weight: 500;">
      ✅ Appointment has been successfully cancelled.
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success text-center" style="border-radius: 15px; font-weight: 500;">
      ✅ Appointment has been successfully updated.
    </div>
  <?php endif; ?>

  <!-- Formular de filtrare -->
  <form method="GET" class="mb-4 d-flex justify-content-center gap-3 flex-wrap">
    <input type="date" name="date" class="form-control" style="max-width: 200px;" value="<?= $_GET['date'] ?? '' ?>">
    <select name="service" class="form-select" style="max-width: 200px;">
      <option value="">All Services</option>
      <?php
      $services = ["Consultation", "Vaccination", "Surgery", "Grooming", "Others"];
      foreach ($services as $service) {
        $selected = ($_GET['service'] ?? '') == $service ? 'selected' : '';
        echo "<option value='$service' $selected>$service</option>";
      }
      ?>
    </select>
    <button type="submit" class="btn" style="background-color: #c89f68; color: white; border-radius: 30px; padding: 8px 20px; font-weight: 500;">Filter</button>
    <a href="dashboard.php" class="btn" style="background-color: #5cb85c; color: white; border-radius: 30px; padding: 8px 20px; font-weight: 500;">Reset</a>
  </form>

  <!-- Tabel programări -->
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
        // Construire interogare cu filtre
        $query = "SELECT * FROM appointments";
        $conditions = [];

        if (!empty($_GET['date'])) {
          $conditions[] = "DATE(appointment_date) = '" . $conn->real_escape_string($_GET['date']) . "'";
        }

        if (!empty($_GET['service'])) {
          $conditions[] = "reason = '" . $conn->real_escape_string($_GET['service']) . "'";
        }

        if ($conditions) {
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
            <button class="btn btn-sm"
              style="background-color: #dc3545; color: white; border-radius: 30px; padding: 6px 14px; font-weight: 500;"
              data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $row['id'] ?>">
              Cancel
            </button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Footer -->
<footer>
  <small>VetCare Clinic &copy; 2025 - All rights reserved</small>
</footer>

<!-- Funcție de filtrare tabel -->
<script>
function filterTable() {
  const input = document.getElementById("searchInput");
  const filter = input.value.toLowerCase();
  const rows = document.querySelectorAll(".table tbody tr");

  rows.forEach(row => {
    const cells = row.querySelectorAll("td");
    const text = [cells[1], cells[2], cells[3]].map(cell => cell.textContent.toLowerCase()).join(" ");
    row.style.display = text.includes(filter) ? "" : "none";
  });
}
</script>

<!-- Modal pentru confirmare ștergere -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Confirm Cancellation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this appointment? It will be <strong>deleted permanently</strong>.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background-color: #c89f68; color: white; border-radius: 30px;" data-bs-dismiss="modal">Back</button>
        <a id="confirmDeleteBtn" href="#" class="btn btn-danger" style="border-radius: 30px;">Yes, Delete</a>
      </div>
    </div>
  </div>
</div>

<!-- Script care actualizează linkul de ștergere în modal -->
<script>
  const deleteModal = document.getElementById('deleteModal');
  deleteModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    document.getElementById('confirmDeleteBtn').href = 'delete_appointment.php?id=' + id;
  });
</script>

</body>
</html>
