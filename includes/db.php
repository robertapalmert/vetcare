<?php 
// Conectare la baza de date MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vetcare";

// Crearea conexiunii
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificarea conexiunii
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
