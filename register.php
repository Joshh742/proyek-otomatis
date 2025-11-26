<?php
session_start();

// 1. MEMUAT KONEKSI DATABASE
require_once './app/config/config.php';

$message = '';
$message_type = ''; // 'error' atau 'success'

// 2. PINDAH KE HALAMAN UTAMA (JIKA SUDAH LOGIN)
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 3. PROSES REGISTRASI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi Input Sederhana
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $message = "Semua kolom wajib diisi!";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Konfirmasi password tidak cocok!";
        $message_type = "error";
    } else {
        // Cek username 
        $checkSql = "SELECT id FROM users WHERE username = :username";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute(['username' => $username]);

        if ($checkStmt->rowCount() > 0) {
            $message = "Username sudah digunakan, pilih yang lain!";
            $message_type = "error";
        } else {
            // ENKRIPSI PASSWORD 
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Masukkan ke Database
            $sql = "INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute(['username' => $username, 'password_hash' => $password_hash])) {
                $message = "Registrasi berhasil! Silakan login.";
                $message_type = "success";
                header("refresh:2;url=login.php");
            } else {
                $message = "Terjadi kesalahan saat mendaftar.";
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrasi - Sistem Inventaris Obat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%), 
                        url('./images/beckground2.avif') center/cover no-repeat fixed;
            position: relative;
            overflow: hidden;
        }

        .bg-animation { position: absolute; width: 100%; height: 100%; overflow: hidden; z-index: 0; }
        .medical-icon { position: absolute; font-size: 40px; opacity: 0.1; animation: float 20s infinite; }
        .medical-icon:nth-child(1) { left: 10%; top: 20%; animation-delay: 0s; }
        .medical-icon:nth-child(2) { left: 80%; top: 30%; animation-delay: 3s; }
        .medical-icon:nth-child(3) { left: 20%; top: 70%; animation-delay: 6s; }
        .medical-icon:nth-child(4) { left: 70%; top: 60%; animation-delay: 9s; }
        .medical-icon:nth-child(5) { left: 50%; top: 10%; animation-delay: 12s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            25% { transform: translateY(-30px) rotate(5deg); }
            50% { transform: translateY(-60px) rotate(-5deg); }
            75% { transform: translateY(-30px) rotate(3deg); }
        }

        .login-container { position: relative; z-index: 1; animation: slideIn 0.6s ease-out; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        
        form { 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: #333;
            border-radius: 25px; 
            padding: 40px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 420px; 
        }

        .header-section { text-align: center; margin-bottom: 25px; }
        .logo {
            width: 70px; height: 70px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px; animation: pulse 2s infinite;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        .logo img { width: 100%; height: 100%; object-fit: contain; padding: 5px; }
        
        h2 { 
            font-size: 1.8em; margin-bottom: 5px; font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .input-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 0.9em; }
        
        input[type="text"], input[type="password"] { 
            width: 100%; padding: 12px 20px; border: 2px solid #e0e0e0; border-radius: 12px; 
            background-color: #f8f9fa; color: #333; font-size: 1em; transition: all 0.3s ease;
        }
        input:focus { outline: none; border-color: #667eea; background-color: #fff; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        
        button { 
            width: 100%; padding: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none; border-radius: 12px; cursor: pointer; font-size: 1.1em;
            font-weight: 700; margin-top: 5px; transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5); }

        .message { 
            padding: 12px; border-radius: 10px; text-align: center; margin-top: 15px; font-weight: 500; font-size: 0.9em;
        }
        .error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }

        .link-text { text-align: center; margin-top: 20px; font-size: 0.9em; color: #666; }
        .link-text a { color: #667eea; text-decoration: none; font-weight: 700; }
        .link-text a:hover { text-decoration: underline; }

    </style>
</head>
<body>
    <div class="bg-animation">
        <div class="medical-icon">⚕️</div>
        <div class="medical-icon">💉</div>
        <div class="medical-icon">🏥</div>
        <div class="medical-icon">💊</div>
        <div class="medical-icon">🩺</div>
    </div>

    <div class="login-container">
        <form action="register.php" method="POST">
            <div class="header-section">
                <div class="logo">
                    <img src="./images/logo_unklab.png" alt="Logo Kampus">
                </div>
                <h2>Buat Akun Baru</h2>
                <p class="subtitle">Daftarkan diri Anda ke sistem</p>
            </div>
            
            <div class="input-group">
                <label for="username">Username</label> 
                <input type="text" id="username" name="username" placeholder="Buat username baru" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label> 
                <input type="password" id="password" name="password" placeholder="Buat password" required>
            </div>
            <div class="input-group">
                <label for="confirm_password">Konfirmasi Password</label> 
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
            </div>

            <button type="submit">Daftar Sekarang</button>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="link-text">
                Sudah punya akun? <a href="login.php">Login disini</a>
            </div>
        </form>
    </div>
</body>
</html>