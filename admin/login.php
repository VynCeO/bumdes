<?php
session_start();
require_once '../config.php';

if (admin_login()) redirect('index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $pass     = $_POST['password'] ?? '';

    if (empty($username) || empty($pass)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin_user WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (hash('sha256', $pass) === $user['password']) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama']     = $user['nama_lengkap'];
                redirect('index.php');
            } else {
                $error = 'Username atau password salah!';
            }
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - BUMDes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
<div class="login-box">
    <div class="login-icon">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 20V14C12 9.58172 15.5817 6 21 6C26.4183 6 30 9.58172 30 14V20M10 20H32C33.1046 20 34 20.8954 34 22V38C34 39.1046 33.1046 40 32 40H10C8.89543 40 8 39.1046 8 38V22C8 20.8954 8.89543 20 10 20Z" stroke="#1f5b3a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            <circle cx="21" cy="29" r="2" fill="#1f5b3a"/>
        </svg>
    </div>
    <h1>Login Admin</h1>
    <p>Akses Panel Administrasi</p>

    <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;padding:12px;font-size:15px;">Login</button>
    </form>

    <div style="text-align:center;margin-top:18px;">
        <a href="../index.php" style="color:#1f5b3a;font-size:14px;">← Kembali ke Website</a>
    </div>
</div>
</body>
</html>
