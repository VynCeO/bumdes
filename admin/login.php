<?php
session_start();
require_once '../config.php';

// Kalau sudah login, langsung ke dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin_user WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hash = hash('sha256', $password);

            if ($hash === $user['password']) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama_lengkap'];
                header('Location: index.php');
                exit;
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
    <h1>🌿 Admin Login</h1>
    <p>BUMDes Sukses Bersama</p>

    <?php if ($error): ?>
    <div class="alert alert-error"><?= $error ?></div>
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
        <button type="submit" class="btn-primary" style="width:100%; padding:12px; font-size:15px;">
            Login
        </button>
    </form>

    <div style="text-align:center; margin-top:20px;">
        <a href="../index.php" style="color:#1f5b3a; font-size:14px; text-decoration:none;">
            ← Kembali ke Website
        </a>
    </div>
</div>

</body>
</html>
