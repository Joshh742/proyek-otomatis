<?php
// KONEKSI DATABASE //
$db_host = 'localhost';
$db_name = 'proyek_otomatis_db';
$db_user = 'proyek_user';
$db_pass = 'Devops@21'; 

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
try {
   
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>