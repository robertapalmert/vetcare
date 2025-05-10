<?php
session_start();
require_once '../includes/db.php';

// Verifică dacă adminul este autentificat
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

$emailMessage = $passwordMessage = "";

// Preluăm datele actuale ale adminului
$stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
$stmt->bind_param("s", $_SESSION["admin_email"]);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Procesare formular
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Actualizare email
    if (!empty($_POST["new_email"])) {
        $newEmail = trim($_POST["new_email"]);

        // Verificare dacă emailul este valid
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $emailMessage = "Invalid email format!";
        } else {
            $stmt = $conn->prepare("UPDATE admin SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $newEmail, $admin['id']);
            $stmt->execute();
            $_SESSION["admin_email"] = $newEmail;
            $emailMessage = "Email updated successfully!";
        }
    }

    // Actualizare parolă
    if (!empty($_POST["new_password"])) {
        $newPassword = trim($_POST["new_password"]);

        if (strlen($newPassword) < 6) {
            $passwordMessage = "Password must be at least 6 characters!";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $admin['id']);
            $stmt->execute();
            $passwordMessage = "Password updated successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Settings - VetCare</title>
  <link rel="icon" type="image/png" href="/vetcare_project/assets/images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Quicksand', sans-serif;
      background: linear-gradient(to bottom right, #fffdf5, #f3e0c7);
      min-height: 100vh;
      padding-top: 40px;
    }

    .settings-box {
      max-width: 600px;
      margin: auto;
      background-color: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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

    .message {
      margin-top: 15px;
      font-weight: 500;
    }

    .text-success {
      color: green;
    }

    .text-danger {
      color: red;
    }
    .form-control:focus, .form-select:focus {
      border-color: #d4a75a !important;
      box-shadow: 0 0 0 0.2rem rgba(212, 167, 90, 0.25);
      outline: none;
    }

  </style>
</head>
<body>

<!-- Cutie cu formularul pentru setările contului -->
<div class="settings-box">
  <h3 class="text-center mb-4">Account Settings</h3>

  <form method="POST">
    <!-- Afișare email curent -->
    <div class="mb-3">
      <label for="current_email">Current Email:</label>
      <input type="email" class="form-control" id="current_email" value="<?= htmlspecialchars($admin['email']) ?>" disabled>
    </div>

    <!-- Câmp pentru email nou -->
    <div class="mb-3">
      <label for="new_email">New Email:</label>
      <input type="email" name="new_email" id="new_email" class="form-control" placeholder="Enter new email">
      <?php if ($emailMessage): ?>
        <div class="message <?= strpos($emailMessage, 'success') !== false ? 'text-success' : 'text-danger' ?>">
          <?= $emailMessage ?>
        </div>
      <?php endif; ?>
    </div>

    <hr>

    <!-- Câmp pentru parolă nouă -->
    <div class="mb-3">
      <label for="new_password">New Password:</label>
      <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password">
      <div class="form-text">Password must be at least 6 characters.</div>
      <?php if ($passwordMessage): ?>
        <div class="message <?= strpos($passwordMessage, 'success') !== false ? 'text-success' : 'text-danger' ?>">
          <?= $passwordMessage ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Butoane salvare / revenire -->
    <div class="text-center mt-4">
      <button type="submit" class="btn btn-save">
        Save Changes
      </button>

      <a href="dashboard.php" class="btn" style="background-color: #c89f68; color: white; font-weight: 500; padding: 8px 20px; border-radius: 30px; margin-left: 10px;">
        Cancel
      </a>
    </div>
  </form>
</div>

</body>
</html>
