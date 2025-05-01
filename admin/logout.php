<?php
session_start(); // Pornim sesiunea pentru a avea acces la variabilele de sesiune
session_destroy(); // Distrugem toate datele sesiunii (delogare completă)
header("Location: login.php"); // Redirecționare către pagina de autentificare
exit;
?>
