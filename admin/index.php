<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

$total_reservasi = $conn->query("SELECT COUNT(*) as c FROM reservasi")->fetch_assoc()['c'];
$pending         = $conn->query("SELECT COUNT(*) as c FROM reservasi WHERE status='pending'")->fetch_assoc()['c'];
$total_produk    = $conn->query("SELECT COUNT(*) as c FROM unit_usaha")->fetch_assoc()['c'];
$total_pimpinan  = $conn->query("SELECT COUNT(*) as c FROM pimpinan")->fetch_assoc()['c'];
$total_users     = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];

$reservasi_baru = $conn->query("SELECT r.*, u.nama as uname FROM reservasi r LEFT JOIN users u ON r.user_id=u.id ORDER BY r.created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - BUMDes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">
<?php render_sidebar('index.php'); ?>

<div class="main-content">
    <div class="top-bar">
        <h1>Dashboard Admin</h1>
        <span><?= date('d F Y') ?></span>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="angka"><?= $total_reservasi ?></div>
            <div class="label">Total Reservasi</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon">⏳</div>
            <div class="angka"><?= $pending ?></div>
            <div class="label">Reservasi Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="angka"><?= $total_produk ?></div>
            <div class="label">Total Produk</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👔</div>
            <div class="angka"><?= $total_pimpinan ?></div>
            <div class="label">Data Pimpinan</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon">👥</div>
            <div class="angka"><?= $total_users ?></div>
            <div class="label">User Terdaftar</div>
        </div>
    </div>

    <div class="table-box">
        <h2>📋 Reservasi Terbaru</h2>
        <table>
            <thead>
                <tr><th>#</th><th>Nama</th><th>Layanan</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php if ($reservasi_baru->num_rows > 0): ?>
            <?php $no = 1; while ($r = $reservasi_baru->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>
                    <strong><?= htmlspecialchars($r['nama']) ?></strong><br>
                    <small style="color:#aaa;"><?= htmlspecialchars($r['no_hp']) ?></small>
                    <?php if ($r['uname']): ?><br><small style="color:#bbb;">via: <?= htmlspecialchars($r['uname']) ?></small><?php endif; ?>
                </td>
                <td><?= htmlspecialchars($r['layanan']) ?></td>
                <td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td>
                <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                <td><a href="manage_reservasi.php?mode=edit&id=<?= $r['id'] ?>" class="btn-sm btn-hijau">Detail</a></td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="6" style="text-align:center;color:#999;padding:20px;">Belum ada reservasi</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="../index.php" target="_blank" class="btn-sm btn-hijau" style="padding:10px 18px;">🌐 Lihat Website</a>
        <a href="manage_reservasi.php" class="btn-sm btn-orange" style="padding:10px 18px;">📅 Kelola Reservasi</a>
        <a href="manage_produk.php" class="btn-sm btn-hijau" style="padding:10px 18px;">🏪 Kelola Produk</a>
        <a href="manage_users.php" class="btn-sm btn-orange" style="padding:10px 18px;">👥 Kelola Users</a>
    </div>
</div>
</div>
</body>
</html>
