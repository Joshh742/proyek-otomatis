<?php
// 1. MEMUAT KONFIGURASI DAN KONEKSI DATABASE
// Ini akan membuat variabel $pdo tersedia
require_once './app/config/config.php';

// VARIABEL UNTUK EDIT FORM
$isEditing = false;
$editData = ['id' => '', 'nama' => '', 'jurusan' => ''];

// =============================================
// BAGIAN 1: LOGIKA CRUD (PROSES C, U, D)
// =============================================

// Cek apakah ada data yang dikirim (form di-submit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- LOGIKA CREATE (TAMBAH DATA) ---
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $nama = $_POST['nama'];
        $jurusan = $_POST['jurusan'];
        
        $sql = "INSERT INTO mahasiswa (nama, jurusan) VALUES (:nama, :jurusan)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nama' => $nama, 'jurusan' => $jurusan]);
        
        // Redirect untuk mencegah form di-submit ulang saat refresh
        header("Location: index.php");
        exit;
    }
    
    // --- LOGIKA UPDATE (UBAH DATA) ---
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $jurusan = $_POST['jurusan'];
        
        $sql = "UPDATE mahasiswa SET nama = :nama, jurusan = :jurusan WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nama' => $nama, 'jurusan' => $jurusan, 'id' => $id]);
        
        header("Location: index.php");
        exit;
    }
}

// --- LOGIKA DELETE (HAPUS DATA) ---
// Cek apakah ada perintah 'delete' dari URL
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "DELETE FROM mahasiswa WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    
    header("Location: index.php");
    exit;
}

// --- LOGIKA UNTUK TAMPILKAN FORM EDIT ---
// Cek apakah ada perintah 'edit' dari URL
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $isEditing = true;
    $id = $_GET['id'];
    
    $sql = "SELECT * FROM mahasiswa WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}


// =============================================
// BAGIAN 2: TAMPILKAN DATA (READ)
// =============================================
// Selalu ambil data terbaru untuk ditampilkan di tabel
$stmt = $pdo->query('SELECT * FROM mahasiswa ORDER BY nama ASC');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>CRUD Mahasiswa</title>
    <style>
        body { font-family: sans-serif; margin: 40px; text-align: center; }
        table { border-collapse: collapse; width: 600px; margin: 20px auto; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        form { width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; text-align: left; }
        form div { margin-bottom: 15px; }
        form label { display: block; margin-bottom: 5px; font-weight: bold; }
        form input[type="text"] { width: 95%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 15px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button.update { background-color: #28a745; }
        button.delete { background-color: #dc3545; padding: 5px 10px; font-size: 0.8em; }
        a { text-decoration: none; color: #007BFF; }
        a.cancel { color: #6c757d; margin-left: 10px; }
    </style>
</head>
<body>

    <h1>Daftar Mahasiswa (CRUD)</h1>
    
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jurusan</th>
                <th colspan="2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                echo "<td>" . htmlspecialchars($row['jurusan']) . "</td>";
                
                // Link untuk EDIT
                echo '<td><a href="index.php?action=edit&id=' . $row['id'] . '">Edit</a></td>';
                
                // Link untuk DELETE (dengan konfirmasi JavaScript)
                echo '<td><a href="index.php?action=delete&id=' . $row['id'] . '" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\');">Hapus</a></td>';
                
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <hr>

    <?php if ($isEditing): ?>
        
        <h2>Edit Mahasiswa</h2>
        <form action="index.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($editData['id']); ?>">
            <div>
                <label for="nama">Nama:</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($editData['nama']); ?>" required>
            </div>
            <div>
                <label for="jurusan">Jurusan:</label>
                <input type="text" id="jurusan" name="jurusan" value="<?php echo htmlspecialchars($editData['jurusan']); ?>" required>
            </div>
            <button type="submit" class="update">Update Data</button>
            <a href="index.php" class="cancel">Batal</a>
        </form>

    <?php else: ?>

        <h2>Tambah Mahasiswa Baru</h2>
        <form action="index.php" method="POST">
            <input type="hidden" name="action" value="create">
            <div>
                <label for="nama">Nama:</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama..." required>
            </div>
            <div>
                <label for="jurusan">Jurusan:</label>
                <input type="text" id="jurusan" name="jurusan" placeholder="Masukkan jurusan..." required>
            </div>
            <button type="submit">Tambah Data</button>
        </form>

    <?php endif; ?>

</body>
</html>