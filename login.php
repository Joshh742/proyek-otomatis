<?php
session_start();

// 1. MEMUAT KONEKSI DATABASE
require_once './app/config/config.php';

$error_message = '';

// SENSOR DETEKSI (TAMBAHAN)
function is_suspicious($input) {
    $suspicious_keywords = [
        "' OR '",
        "--",
        "UNION SELECT",
        "DROP TABLE",
        "'='",
        "/*"
    ];
    foreach ($suspicious_keywords as $keyword) {
        if (stripos($input, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// 2. PINDAH KE HALAMAN UTAMA (JIKA SUDAH LOGIN)
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 3. PROSES LOGIN 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // LOGIN BERHASIL
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        header("Location: index.php");
        exit;
    } else {
        // LOGIN GAGAL
        $error_message = "Username atau password salah!";
        
        // Cek Input Username
        if (is_suspicious($username)) {
            $ip_pelaku = $_SERVER['REMOTE_ADDR'];
            $log_entry = date('Y-m-d H:i:s') . " | IP: $ip_pelaku | Payload: $username\n";
            
            // Simpan ke Log File
           file_put_contents('/var/www/html/unkpresent/logs/sqli_attempts.log', $log_entry, FILE_APPEND);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Inventaris obat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .bg-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .medical-icon {
            position: absolute;
            font-size: 40px;
            opacity: 0.1;
            animation: float 20s infinite;
        }

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

        .login-container {
            position: relative;
            z-index: 1;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from { 
                opacity: 0;
                transform: translateY(-30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        form { 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: #333;
            border-radius: 25px; 
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 420px; 
            box-sizing: border-box;
        }

        .header-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 5px;
        }

        .logo::before {
            display: none;
        }
        
        h2 { 
            font-size: 2em;
            margin-bottom: 8px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            color: #666;
            font-size: 0.95em;
            font-weight: 500;
        }
        
        .input-group { 
            margin-bottom: 25px;
            position: relative;
        }
        
        label { 
            display: block; 
            margin-bottom: 10px; 
            font-weight: 600; 
            color: #555;
            font-size: 0.95em;
        }
        
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 15px 20px; 
            border: 2px solid #e0e0e0; 
            border-radius: 12px; 
            background-color: #f8f9fa;
            color: #333; 
            font-size: 1em;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        ::placeholder {
            color: #999;
        }
        
        button { 
            width: 100%; 
            padding: 16px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            font-size: 1.1em;
            font-weight: 700;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        button:active {
            transform: translateY(0);
        }
        
        .footer-text {
            font-size: 0.85em;
            color: #888;
            text-align: center;
            margin-top: 25px;
            line-height: 1.6;
        }

        .error { 
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 14px 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 0;
            font-weight: 500;
            animation: shake 0.5s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .medical-cross {
            position: absolute;
            top: -5px;
            right: 15px;
            color: #667eea;
            font-size: 24px;
            opacity: 0.3;
        }
    
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
        <form action="login.php" method="POST">
            <div class="header-section">
                <div class="logo">
                    <img src="./images/logo_unklab.png" alt="Logo Kampus">
                </div>
                <h2>Ivan Kaseger</h2>
                <p class="subtitle">Kelola stok obat dengan mudah</p>
            </div>
            
            <div class="input-group">
                <label for="username">Username</label> 
                <input type="text" id="username" name="username" placeholder="Masukkan username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label> 
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit">Login</button>

            <?php if ($error_message): ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 20px; font-size: 0.9em; color: #666;">
                Belum punya akun? <a href="register.php" style="color: #667eea; text-decoration: none; font-weight: 700;">Daftar disini</a>
            </div>
            
            <div class="footer-text">
                Data Anda aman dan terenkripsi.<br>
                Sistem manajemen farmasi terpercaya.
            </div>
        </form>
    </div>
</body>
</html>