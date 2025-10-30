<?phpw
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit; 
}

require_once './app/config/config.php';

$isEditing = false;
$editData = ['id' => '', 'nama' => '', 'jurusan' => ''];

// BAGIAN 1: LOGIKA CRUD 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $nama = $_POST['nama']; $jurusan = $_POST['jurusan'];
        $sql = "INSERT INTO mahasiswa (nama, jurusan) VALUES (:nama, :jurusan)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nama' => $nama, 'jurusan' => $jurusan]);
        header("Location: index.php"); exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = $_POST['id']; $nama = $_POST['nama']; $jurusan = $_POST['jurusan'];
        $sql = "UPDATE mahasiswa SET nama = :nama, jurusan = :jurusan WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nama' => $nama, 'jurusan' => $jurusan, 'id' => $id]);
        header("Location: index.php"); exit;
    }
}
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM mahasiswa WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    header("Location: index.php"); exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $isEditing = true;
    $id = $_GET['id'];
    $sql = "SELECT * FROM mahasiswa WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}
// BAGIAN 2: TAMPILKAN DATA (READ)

$stmt = $pdo->query('SELECT * FROM mahasiswa ORDER BY nama ASC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>CRUD Mahasiswa</title>
    <style>
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            background-color: #f9f9f9;
        }
        
        .container {
            width: 800px; 
            margin: 40px auto; 
            text-align: center;
        }

        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin: 20px auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden; 
        }
        
        th, td { 
            border-bottom: 1px solid #ddd;
            padding: 16px; 
            text-align: left; 
        }

        thead th {
            background-color: #2c3e50; 
            color: white;
            border-bottom: 0;
        }
        
        tbody tr {
            background-color: #ffffff;
        }
        tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }
        
        td.actions {
            text-align: center;
        }
        td.actions a {
            text-decoration: none;
            font-size: 1.5em; 
            margin: 0 8px;
            cursor: pointer;
        }

        form { 
            width: 100%; 
            margin: 40px auto; 
            padding: 30px; 
            background: #ffffff;
            border: 1px solid #ddd; 
            border-radius: 8px; 
            text-align: left; 
            box-sizing: border-box; 
        }
        form div { margin-bottom: 15px; }
        form label { display: block; margin-bottom: 5px; font-weight: bold; }
        form input[type="text"] { width: 95%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; }
        
        button[type="submit"] { 
            padding: 12px 25px; 
            background-color: #b0b0b0; 
            color: white; 
            border: none; 
            border-radius: 20px; 
            cursor: pointer; 
            font-size: 1em;
            font-weight: bold;
            display: block;
            margin: 20px auto 0 auto; 
        }
        
        button.update { background-color: #28a745; border-radius: 20px; }
        a.cancel { color: #6c757d; margin-left: 10px; }

    </style>
</head>
<body>
    <div class="container">
        
    <div style="text-align: right; margin-bottom: 20px;">
        Halo, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
        <a href="logout.php" style="margin-left: 15px;">Logout</a>
    </div>

    <div class="container"> <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jurusan</th>
                    <th colspan="2" style="text-align: center;">Aksi</th> </tr>
            </thead>
            <tbody>
                <?php
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['jurusan']) . "</td>";
                    
                    echo '<td class="actions"><a href="index.php?action=edit&id=' . $row['id'] . '">✏️</a></td>';
                    echo '<td class="actions"><a href="index.php?action=delete&id=' . $row['id'] . '" onclick="return confirm(\'Yakin hapus data ini?\');">🗑️</a></td>';
                    
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>


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
                <button type="submit">tambah student</button>
            </form>

        <?php endif; ?>

    </div> </body>
</html>