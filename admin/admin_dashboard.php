<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION["admin_logged_in"])) {
  header("Location: admin_login.php");
  exit;
}

$result = $conn->query("SELECT * FROM appointments ORDER BY appointment_date ASC");
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
      max-width: 1200px;
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
      <a href="../index.html" class="nav-link d-inline">Home</a>
      <a href="admin_logout.php" class="nav-link d-inline">Logout</a>
    </nav>
  </div>
</header>

<div class="dashboard">
  <h3 class="text-center mb-4">All Appointments</h3>
  <table class="table table-bordered text-center">
    <thead>
      <tr>
        <th>ID</th>
        <th>Pet Name</th>
        <th>Owner</th>
        <th>Phone</th>
        <th>Date</th>
        <th>Reason</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['pet_name'] ?></td>
        <td><?= $row['owner_name'] ?></td>
        <td><?= $row['phone'] ?></td>
        <td><?= $row['appointment_date'] ?></td>
        <td><?= $row['reason'] ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<footer>
  <small>VetCare Clinic &copy; 2025 - All rights reserved</small>
</footer>

</body>
</html>