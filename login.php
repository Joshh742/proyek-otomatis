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
    <title>Login</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            background-color: #ffffff; 
            margin: 0;
        }
        
        form { 
            background: #004a99; 
            color: white;
            border-radius: 20px; 
            padding: 40px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            width: 380px; 
            box-sizing: border-box;
        }
        
        h2 { 
            text-align: left; 
            font-size: 2em;
            margin-top: 0;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        div { margin-bottom: 20px; }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 500; 
            text-align: left;
        }
        
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 12px 15px; 
            border: 1px solid #5c9cff; 
            border-radius: 8px; 
            background-color: transparent; 
            color: white; 
            box-sizing: border-box;
            font-size: 1em;
        }
        
        ::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        button { 
            width: 100%; 
            padding: 12px; 
            background-color: #2ea8ff; 
            color: white; 
            border: none; 
            border-radius: 50px; 
            cursor: pointer; 
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 20px;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #5bc0ff;
        }
        
        .footer-text {
            font-size: 0.8em;
            color: #cce1ff;
            text-align: center;
            margin-top: 20px;
            line-height: 1.4;
        }

        .error { 
            background: #ff4d4d;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-top: 15px;
            margin-bottom: 0;
        }
    
    </style>
</head>
<body>
    <form action="login.php" method="POST">
        <h2>Salamalekum</h2>
        
        <div>
            <label for="username">Username</label> 
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password</label> 
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>

        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <div class="footer-text">
            Nama Anda akan dibagikan. Jangan pernah mengirimkan kata sandi.
            Pelajari cara kami menangani data Anda
        </div>
    </form>
</body>
</html>