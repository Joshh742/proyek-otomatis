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
        body { font-family: sans-serif; margin: 40px; }
        table { border-collapse: collapse; width: 400px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Daftar Mahasiswa dari Database MySQL</h1>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jurusan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 3. Loop melalui setiap baris data dan menampilkannya sebagai baris tabel
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