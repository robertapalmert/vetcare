<?php
session_start();
require_once '../includes/db.php';

// Verifică dacă adminul este logat
if (!isset($_SESSION["admin_logged_in"])) {
  header("Location: login.php");
  exit;
}

// Verifică dacă avem un ID valid în URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Pregătește și execută ștergerea
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirecționează înapoi la dashboard
        header("Location: dashboard.php?deleted=1");
        exit;
    } else {
        echo "Error deleting appointment: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
