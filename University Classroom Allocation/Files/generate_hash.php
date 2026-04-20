<?php
$password = "admin123"; // Replace with your actual password
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed password: " . $hash;
?>
