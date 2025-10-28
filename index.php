<?php
// 1. Memuat file konfigurasi (yang juga otomatis membuat koneksi $pdo)
require_once './app/config/config.php';

// 2. Menyiapkan dan menjalankan query untuk mengambil data
$stmt = $pdo->query('SELECT nama, jurusan FROM mahasiswa ORDER BY nama ASC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Daftar Mahasiswa</title>
    <style>
        
        body { 
            font-family: sans-serif; 
            margin: 40px; 
            text-align: center; 
        }
        table { 
            border-collapse: collapse; 
            width: 500px; 
            margin: 20px auto; 
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #f4f4f4; 
        }
        img.logo {
            width: 150px; 
            height: auto; 
            margin-bottom: 20px; 
        }
    
    </style>
</head>
<body>
    
    <img src="images\logo_unklab.png" alt="Logo Universitas Klabat" class="logo">

    <h1>Daftar Mahasiswa dari Tabel Database MySQL</h1>
    
    <table>
        <thead>
            <tr>
                <th>nama </th>
                <th>fakultas</th>
            </tr>
        </thead>
        <tbody>
            <?php
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                echo "<td>" . htmlspecialchars($row['jurusan']) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>