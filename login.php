<?php
// SELALU MULAI SESI DI ATAS
session_start();

// 1. MEMUAT KONEKSI DATABASE
// (Pastikan path ini benar sesuai struktur Anda)
require_once './app/config/config.php';

$error_message = '';

// 2. JIKA SUDAH LOGIN, LEMPAR KE INDEX.PHP
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 3. PROSES LOGIN JIKA FORM DI-SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Ambil data user dari database
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifikasi user dan password
    if ($user && password_verify($password, $user['password_hash'])) {
        // Password benar! Simpan data user ke session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Arahkan ke halaman utama
        header("Location: index.php");
        exit;
    } else {
        // Jika salah, tampilkan pesan error
        $error_message = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        /* === CSS BARU DIMULAI DI SINI === */
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            background-color: #ffffff; /* Latar belakang putih */
            margin: 0;
        }
        
        form { 
            background: #004a99; /* Biru tua */
            color: white;
            border-radius: 20px; /* Sudut sangat bulat */
            padding: 40px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            width: 380px; /* Lebar form */
            box-sizing: border-box;
        }
        
        h2 { 
            text-align: left; /* Teks "Selamat Datang" rata kiri */
            font-size: 2em;
            margin-top: 0;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        div { margin-bottom: 20px; }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 500; /* Sedikit tebal */
            text-align: left;
        }
        
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 12px 15px; 
            border: 1px solid #5c9cff; /* Border biru muda */
            border-radius: 8px; 
            background-color: transparent; /* Transparan */
            color: white; /* Teks input putih */
            box-sizing: border-box;
            font-size: 1em;
        }
        
        /* Placeholder text color (jika Anda menambahkannya) */
        ::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        button { 
            width: 100%; 
            padding: 12px; 
            background-color: #2ea8ff; /* Biru muda cerah */
            color: white; 
            border: none; 
            border-radius: 50px; /* Bentuk pil */
            cursor: pointer; 
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 20px;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #5bc0ff;
        }
        
        /* Teks footer di bawah tombol */
        .footer-text {
            font-size: 0.8em;
            color: #cce1ff;
            text-align: center;
            margin-top: 20px;
            line-height: 1.4;
        }

        /* Pesan error */
        .error { 
            background: #ff4d4d;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-top: 15px;
            margin-bottom: 0;
        }
        /* === CSS BARU BERAKHIR DI SINI === */
    </style>
</head>
<body>
    
    <form action="login.php" method="POST">
        <h2>Selamat Datang</h2>
        
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