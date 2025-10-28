<?php
require_once './app/config/config.php';

// ATUR USERNAME DAN PASSWORD ANDA DI SINI
$username = 'devops';
$password = 'Devops@21'; // Ganti dengan password yang kuat

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Masukkan ke database
$sql = "INSERT INTO users (username, password_hash) VALUES (:username, :pass)";
$stmt = $pdo->prepare($sql);
$stmt->execute(['username' => $username, 'pass' => $hashed_password]);

echo "User '$username' berhasil dibuat dengan password '$password'. HAPUS FILE INI SEKARANG!";
?>