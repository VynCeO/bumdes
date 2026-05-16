<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

$pesan = '';

// HAPUS USER
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Set user_id di reservasi jadi NULL sebelum hapus
    $conn->query("UPDATE reservasi SET user_id=NULL WHERE user_id=$id");
    $conn->query("DELETE FROM users WHERE id=$id");
    $pesan = '✅ User berhasil dihapus.';
}

$users = $conn->query("SELECT u.*, COUNT(r.id) as total_reservasi FROM users u LEFT JOIN reservasi r ON r.user_id=u.id GROUP BY u.id ORDER BY u.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Users - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">
<?php render_sidebar('manage_users.php'); ?>

<div class="main-content">
    <div class="top-bar">
        <h1>👥 Kelola Users</h1>
        <span style="color:#888;font-size:14px;">Daftar pengguna yang terdaftar</span>
    </div>

    <?php if ($pesan): ?>
    <div class="alert alert-success" style="margin-bottom:15px;"><?= $pesan ?></div>
    <?php endif; ?>

    <div class="table-box">
        <table>
            <thead>
                <tr><th>#</th><th>Nama</th><th>Email</th><th>No. HP</th><th>Reservasi</th><th>Daftar</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php if ($users->num_rows > 0): ?>
            <?php $no = 1; while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($u['nama']) ?></strong></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['no_hp'] ?? '-') ?></td>
                <td style="text-align:center;">
                    <span class="badge <?= $u['total_reservasi'] > 0 ? 'badge-confirmed' : 'badge-pending' ?>">
                        <?= $u['total_reservasi'] ?> reservasi
                    </span>
                </td>
                <td style="color:#888;font-size:13px;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <a href="?aksi=hapus&id=<?= $u['id'] ?>" class="btn-sm btn-merah"
                       onclick="return confirm('Hapus user <?= htmlspecialchars($u['nama']) ?>?')">🗑 Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="7" style="text-align:center;color:#999;padding:20px;">Belum ada user terdaftar</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</body>
</html>
