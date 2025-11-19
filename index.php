<?php
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
    <meta charset="UTF-8">
    <title>Sistem Inventaris Obat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.85) 0%, rgba(118, 75, 162, 0.85) 100%),
                        url('./images/opah.jpg') center/cover no-repeat fixed;
            min-height: 100vh;
            padding: 20px;
            position: relative;
        }

        /* Animasi Latar Belakang */
        .bg-animation {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .floating-icon {
            position: absolute;
            font-size: 30px;
            opacity: 0.08;
            animation: float 25s infinite;
        }

        .floating-icon:nth-child(1) { left: 5%; top: 10%; animation-delay: 0s; }
        .floating-icon:nth-child(2) { left: 85%; top: 20%; animation-delay: 5s; }
        .floating-icon:nth-child(3) { left: 15%; top: 80%; animation-delay: 10s; }
        .floating-icon:nth-child(4) { left: 75%; top: 70%; animation-delay: 15s; }
        .floating-icon:nth-child(5) { left: 45%; top: 15%; animation-delay: 20s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            25% { transform: translateY(-40px) rotate(10deg); }
            50% { transform: translateY(-80px) rotate(-10deg); }
            75% { transform: translateY(-40px) rotate(5deg); }
        }
        
        .container {
            max-width: 1200px; 
            margin: 0 auto;
            position: relative;
            z-index: 1;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 25px 35px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-header {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo-header img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 3px;
        }

        .header h1 {
            font-size: 1.8em;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info strong {
            color: #333;
            font-size: 1.05em;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        /* Tabel */
        .table-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .table-header h2 {
            font-size: 1.5em;
            color: #333;
            font-weight: 700;
        }

        .stats {
            display: flex;
            gap: 20px;
        }

        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin: 0;
            border-radius: 12px;
            overflow: hidden;
        }
        
        th, td { 
            padding: 18px 20px; 
            text-align: left; 
        }

        thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            font-size: 1.05em;
            border: none;
        }
        
        tbody tr {
            background-color: #ffffff;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background-color: #f8f9ff;
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        tbody tr:last-child {
            border-bottom: none;
        }
        
        td.actions {
            text-align: center;
        }

        td.actions a {
            text-decoration: none;
            font-size: 1.4em; 
            margin: 0 8px;
            cursor: pointer;
            display: inline-block;
            transition: all 0.3s ease;
        }

        td.actions a:hover {
            transform: scale(1.3);
        }

        /* Form */
        .form-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        form h2 {
            margin-bottom: 25px;
            font-size: 1.6em;
            color: #333;
            font-weight: 700;
            text-align: center;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group { 
            display: flex;
            flex-direction: column;
        }

        form label { 
            margin-bottom: 10px; 
            font-weight: 600;
            color: #555;
            font-size: 0.95em;
        }

        form input[type="text"] { 
            padding: 15px 18px; 
            border: 2px solid #e0e0e0; 
            border-radius: 12px;
            font-size: 1em;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        form input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        button[type="submit"] { 
            padding: 15px 40px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            font-size: 1.05em;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        button.update { 
            background: linear-gradient(135deg, #51cf66 0%, #37b24d 100%);
            box-shadow: 0 4px 15px rgba(81, 207, 102, 0.4);
        }

        button.update:hover {
            box-shadow: 0 6px 20px rgba(81, 207, 102, 0.5);
        }

        a.cancel { 
            color: white;
            background: linear-gradient(135deg, #868e96 0%, #495057 100%);
            padding: 15px 40px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(134, 142, 150, 0.4);
        }

        a.cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(134, 142, 150, 0.5);
        }

    </style>
</head>
<body>
    <!-- Animasi Latar Belakang -->
    <div class="bg-animation">
        <div class="floating-icon">⚕️</div>
        <div class="floating-icon">💊</div>
        <div class="floating-icon">💉</div>
        <div class="floating-icon">🏥</div>
        <div class="floating-icon">🩺</div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="logo-header">
                    <img src="./images/logo_unklab.png" alt="Logo Kampus">
                </div>
                <h1>Sistem Inventaris Obat</h1>
            </div>
            <div class="user-info">
                <span>👤 Halo, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</span>
                <a href="logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="table-container">
            <div class="table-header">
                <h2>📋 Daftar Obat</h2>
                <div class="stats">
                    <div class="stat-box">
                        📦 Total: <?php echo $stmt->rowCount(); ?> Item
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>💊 Nama Obat</th>
                        <th>🏷️ Kategori</th>
                        <th colspan="2" style="text-align: center;">⚙️ Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['jurusan']) . "</td>";
                        
                        echo '<td class="actions"><a href="index.php?action=edit&id=' . $row['id'] . '" title="Edit">✏️</a></td>';
                        echo '<td class="actions"><a href="index.php?action=delete&id=' . $row['id'] . '" onclick="return confirm(\'Yakin hapus obat ini dari inventaris?\');" title="Hapus">🗑️</a></td>';
                        
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Form -->
        <div class="form-container">
            <?php if ($isEditing): ?>
                
                <form action="index.php" method="POST">
                    <h2>✏️ Edit Data Obat</h2>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editData['id']); ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama">💊 Nama Obat:</label>
                            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($editData['nama']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="jurusan">🏷️ Kategori:</label>
                            <input type="text" id="jurusan" name="jurusan" value="<?php echo htmlspecialchars($editData['jurusan']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="update">✅ Update Data</button>
                        <a href="index.php" class="cancel">❌ Batal</a>
                    </div>
                </form>

            <?php else: ?>

                <form action="index.php" method="POST">
                    <h2>➕ Tambah Obat Baru</h2>
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama">💊 Nama Obat:</label>
                            <input type="text" id="nama" name="nama" placeholder="Contoh: Paracetamol 500mg" required>
                        </div>
                        <div class="form-group">
                            <label for="jurusan">🏷️ Kategori:</label>
                            <input type="text" id="jurusan" name="jurusan" placeholder="Contoh: Analgesik, Antibiotik" required>
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit">➕ Tambah Obat</button>
                    </div>
                </form>

            <?php endif; ?>
        </div>

    </div>
</body>
</html>