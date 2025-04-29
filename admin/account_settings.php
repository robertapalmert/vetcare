<?php
session_start();
require_once '../includes/db.php';

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
    if (!empty($_POST["new_email"])) {
        $newEmail = trim($_POST["new_email"]);
        $stmt = $conn->prepare("UPDATE admin SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $newEmail, $admin['id']);
        $stmt->execute();
        $_SESSION["admin_email"] = $newEmail;
        $emailMessage = "Email updated successfully!";
    }

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
      background-color: #c89f68;
      color: white;
      border-radius: 30px;
      padding: 10px 25px;
      font-weight: 600;
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
  </style>
</head>
<body>

<div class="settings-box">
  <h3 class="text-center mb-4">Account Settings</h3>

  <form method="POST">
    <div class="mb-3">
      <label>Current Email:</label>
      <input type="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" disabled>
    </div>
    <div class="mb-3">
      <label>New Email:</label>
      <input type="email" name="new_email" class="form-control" placeholder="Enter new email">
      <?php if ($emailMessage): ?>
        <div class="message text-success"><?= $emailMessage ?></div>
      <?php endif; ?>
    </div>
    <hr>
    <div class="mb-3">
      <label>New Password:</label>
      <input type="password" name="new_password" class="form-control" placeholder="Enter new password">
      <?php if ($passwordMessage): ?>
        <div class="message <?= strpos($passwordMessage, 'success') !== false ? 'text-success' : 'text-danger' ?>">
          <?= $passwordMessage ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="text-center">
      <button type="submit" class="btn btn-save">Save Changes</button>
    </div>
  </form>
</div>

</body>
</html>
