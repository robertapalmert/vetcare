<?php
session_start();
require_once '../includes/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin) {
        // Simulăm generarea unui link de resetare
        $reset_link = "http://localhost/vetcare_project/admin/reset_password.php?email=" . urlencode($email);
        $message = "Reset link (simulat): <a href='$reset_link'>Reset Your Password</a>";
    } else {
        $message = "No admin found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password - VetCare</title>
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

    .forgot-box {
      background-color: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      max-width: 500px;
      width: 100%;
    }

    .btn-reset {
      background-color: #c89f68;
      color: white;
      border-radius: 30px;
      padding: 10px 25px;
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="forgot-box">
  <h3 class="text-center mb-4">Forgot Password</h3>

  <form method="POST">
    <div class="mb-3">
      <label>Email Address:</label>
      <input type="email" name="email" class="form-control" required />
    </div>
    <div class="text-center">
      <button type="submit" class="btn btn-reset">Send Reset Link</button>
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
