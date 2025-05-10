<?php
session_start();
require_once '../includes/db.php';

$error = "";

// Verificăm dacă s-a trimis formularul
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Căutăm adminul în baza de date după email
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // Verificăm parola
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION["admin_logged_in"] = true;
        $_SESSION["admin_email"] = $admin["email"];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login - VetCare</title>
  <link rel="icon" type="image/png" href="/vetcare_project/assets/images/logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
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
      white-space: nowrap;
    }
    .login-box {
      max-width: 500px;
      margin: 60px auto;
      background-color: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .btn-login {
      background-color: #c89f68;
      color: white;
      border-radius: 30px;
      padding: 10px 25px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .btn-login:hover {
      background-color: #dcb177;
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(0,0,0,0.25);
    }
    .form-control:focus, .form-select:focus {
      border-color: #d4a75a !important;
      box-shadow: 0 0 0 0.2rem rgba(212, 167, 90, 0.25);
      outline: none;
    }
    .reset-link {
      color: #c89f68;
      font-weight: normal;
      font-size: 14px;
      text-decoration: none;
    }
    .reset-link:hover {
      text-decoration: underline;
      color: #c89f68;
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

<!-- Antet cu logo și meniu de navigare -->
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
      <a href="/vetcare_project/public/book.php" class="nav-link d-inline">Book Appointment</a>
    </nav>
  </div>
</header>

<!-- Formularul de autentificare -->
<div class="login-box">
  <h3 class="text-center mb-4">Admin Login</h3>

  <!-- Afișare mesaj de eroare dacă există -->
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger text-center" style="border-radius: 15px; font-weight: 500;">
      ❗ <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label>Email:</label>
      <input type="email" name="email" class="form-control" required />
    </div>
    <div class="mb-4">
      <label>Password:</label>
      <input type="password" name="password" class="form-control" required />
    </div>
    <div class="text-center">
      <button type="submit" class="btn btn-login">Login</button>
    </div>
    <div class="text-center mt-3">
      <a href="forgot_password.php" class="reset-link">Forgot Password?</a>
    </div>
    <div class="text-center mt-2">
      <em style="font-size: 13px; color: #6c757d;">Forgot your email? Please contact the clinic administrator.</em>
    </div>
  </form>
</div>

<!-- Footer -->
<footer>
  <small>VetCare Clinic &copy; 2025 - All rights reserved</small>
</footer>

</body>
</html>
