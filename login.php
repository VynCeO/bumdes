<?php
session_start();
require_once 'config.php';

// Sudah login, balik ke index
if (user_login()) redirect('index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (empty($email) || empty($pass)) {
        $error = 'Email dan password wajib diisi!';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (hash('sha256', $pass) === $user['password']) {
                // Set session
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_nama']  = $user['nama'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_hp']    = $user['no_hp'];

                // Set cookie "ingat saya" 7 hari jika dicentang
                if (!empty($_POST['ingat'])) {
                    setcookie('bumdes_user', $user['email'], time() + (7 * 24 * 3600), '/');
                }

                redirect('index.php');
            } else {
                $error = 'Email atau password salah!';
            }
        } else {
            $error = 'Email atau password salah!';
        }
    }
}

// Isi email dari cookie jika ada
$prefill_email = $_COOKIE['bumdes_user'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BUMDes Sugihwaras</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">

<div class="login-box">
    <div class="login-icon">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="24" cy="12" r="8" fill="#1f5b3a"/>
            <path d="M24 22C16.268 22 10 26.477 10 32v6h28v-6c0-5.523-6.268-10-14-10z" fill="#1f5b3a"/>
        </svg>
    </div>
    <h1>Login User</h1>
    <p>Login untuk melakukan reservasi</p>

    <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($prefill_email) ?>" placeholder="email@gmail.com" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;font-size:14px;">
            <input type="checkbox" name="ingat" id="ingat" style="width:auto;margin:0;">
            <label for="ingat" style="margin:0;font-weight:normal;cursor:pointer;">Ingat saya selama 7 hari</label>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;padding:12px;font-size:15px;">
            Login
        </button>
    </form>

    <div style="text-align:center;margin-top:18px;font-size:14px;">
        Belum punya akun? <a href="register.php" style="color:#1f5b3a;font-weight:bold;">Daftar sekarang</a>
    </div>
    <div style="text-align:center;margin-top:8px;">
        <a href="index.php" style="color:#888;font-size:13px;">← Kembali ke Website</a>
    </div>
</div>

</body>
</html>
