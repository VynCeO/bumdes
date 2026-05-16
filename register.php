<?php
session_start();
require_once 'config.php';

// Sudah login, balik ke index
if (user_login()) redirect('index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $hp       = trim($_POST['no_hp'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $pass2    = $_POST['password2'] ?? '';

    if (empty($nama) || empty($email) || empty($pass)) {
        $error = 'Nama, email, dan password wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($pass) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($pass !== $pass2) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        // Cek email sudah ada
        $cek = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $cek->bind_param('s', $email);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = 'Email sudah terdaftar! Silakan login.';
        } else {
            $hash = hash('sha256', $pass);
            $stmt = $conn->prepare("INSERT INTO users (nama, email, no_hp, password) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $nama, $email, $hp, $hash);
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan, coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - BUMDes Sugihwaras</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">

<div class="login-box" style="max-width:420px;">
    <h1>🌿 Daftar Akun</h1>
    <p>Buat akun untuk melakukan reservasi</p>

    <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom:16px;"><?= $success ?></div>
    <div style="text-align:center;margin-top:10px;">
        <a href="login.php" class="btn-primary">Masuk Sekarang →</a>
    </div>
    <?php else: ?>

    <form method="POST">
        <div class="form-group">
            <label>Nama Lengkap *</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" placeholder="Masukkan nama lengkap" required>
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="email@contoh.com" required>
        </div>
        <div class="form-group">
            <label>No. HP / WhatsApp</label>
            <input type="text" name="no_hp" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx">
        </div>
        <div class="form-group">
            <label>Password * (min. 6 karakter)</label>
            <input type="password" name="password" placeholder="Buat password" required>
        </div>
        <div class="form-group">
            <label>Konfirmasi Password *</label>
            <input type="password" name="password2" placeholder="Ulangi password" required>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;padding:12px;font-size:15px;">
            Daftar Sekarang
        </button>
    </form>

    <?php endif; ?>

    <div style="text-align:center;margin-top:18px;font-size:14px;">
        Sudah punya akun? <a href="login.php" style="color:#1f5b3a;font-weight:bold;">Login di sini</a>
    </div>
    <div style="text-align:center;margin-top:8px;">
        <a href="index.php" style="color:#888;font-size:13px;">← Kembali ke Website</a>
    </div>
</div>

</body>
</html>
