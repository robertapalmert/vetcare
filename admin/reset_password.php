<?php
session_start();
require_once '../includes/db.php';

$email = $_GET['email'] ?? ''; // Preluăm emailul din URL (simulat)
$message = "";

// Când formularul este trimis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = trim($_POST["new_password"]);

    // Verificăm lungimea parolei
    if (strlen($newPassword) < 6) {
        $message = "Password must be at least 6 characters!";
    } else {
        // Parola este criptată și salvată în baza de date
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();
        $message = "Password has been reset! <a href='login.php'>Go to Login</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password - VetCare</title>
  <link rel="icon" type="image/png" href="/vetcare_project/assets/images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Quicksand', sans-serif;
      background: linear-gradient(to bottom right, #fffdf5, #f3e0c7);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .reset-box {
      background-color: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      max-width: 500px;
      width: 100%;
    }

    .btn-save {
      background-color: #c89f68;
      color: white;
      border-radius: 30px;
      padding: 10px 25px;
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="reset-box">
  <h3 class="text-center mb-4">Reset Your Password</h3>

  <form method="POST">
    <div class="mb-3">
      <label>New Password:</label>
      <input type="password" name="new_password" class="form-control" required />
    </div>
    <div class="text-center">
      <button type="submit" class="btn btn-save">Save New Password</button>
    </div>
  </form>

  <?php if ($message): ?>
    <div class="mt-4 text-center">
      <?= $message ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
