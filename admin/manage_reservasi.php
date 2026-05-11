<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

$pesan = '';

// Update status reservasi
if (isset($_GET['aksi']) && $_GET['aksi'] === 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $id     = (int)$_GET['id'];
    $status = $_GET['status'];
    $allowed = ['pending','confirmed','cancelled'];
    if (in_array($status, $allowed)) {
        $conn->query("UPDATE reservasi SET status='$status' WHERE id=$id");
        $pesan = "Status berhasil diubah ke '$status'!";
    }
}

// Hapus reservasi
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM reservasi WHERE id=$id");
    $pesan = 'Reservasi berhasil dihapus.';
}

// Ambil semua reservasi
$filter_status = $_GET['status'] ?? 'ALL';
if ($filter_status !== 'ALL') {
    $stmt = $conn->prepare("SELECT * FROM reservasi WHERE status=? ORDER BY created_at DESC");
    $stmt->bind_param('s', $filter_status);
    $stmt->execute();
    $reservasi_list = $stmt->get_result();
} else {
    $reservasi_list = $conn->query("SELECT * FROM reservasi ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Reservasi - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">

    <?php render_sidebar('manage_reservasi.php'); ?>

    <div class="main-content">
        <div class="top-bar">
            <h1>📅 Kelola Reservasi</h1>
            <span><?= date('d F Y') ?></span>
        </div>

        <?php if ($pesan): ?>
        <div class="alert alert-success" style="margin-bottom:15px;"><?= $pesan ?></div>
        <?php endif; ?>

        <!-- FILTER STATUS -->
        <div style="margin-bottom:15px; display:flex; gap:8px; flex-wrap:wrap;">
            <?php foreach (['ALL','pending','confirmed','cancelled'] as $s): ?>
            <a href="manage_reservasi.php?status=<?= $s ?>"
               class="btn-sm <?= $filter_status === $s ? 'btn-hijau' : 'btn-orange' ?>"
               style="padding:7px 14px;">
                <?= $s === 'ALL' ? 'Semua' : ucfirst($s) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Layanan</th>
                        <th>Tanggal</th>
                        <th>HP</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($reservasi_list->num_rows > 0): ?>
                <?php $no = 1; while ($r = $reservasi_list->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <strong><?= htmlspecialchars($r['nama']) ?></strong><br>
                        <small style="color:#888;"><?= htmlspecialchars($r['email'] ?? '') ?></small>
                    </td>
                    <td><?= htmlspecialchars($r['layanan']) ?></td>
                    <td>
                        <?= date('d/m/Y', strtotime($r['tanggal'])) ?>
                        <?php if ($r['tanggal_kembali']): ?>
                        <br><small>s/d <?= date('d/m/Y', strtotime($r['tanggal_kembali'])) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['no_hp']) ?></td>
                    <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                    <td>
                        <?php if ($r['status'] === 'pending'): ?>
                        <a href="?aksi=update_status&id=<?= $r['id'] ?>&status=confirmed"
                           class="btn-sm btn-hijau" onclick="return confirm('Konfirmasi reservasi ini?')">✓ Konfirmasi</a>
                        <?php endif; ?>
                        <?php if ($r['status'] !== 'cancelled'): ?>
                        <a href="?aksi=update_status&id=<?= $r['id'] ?>&status=cancelled"
                           class="btn-sm btn-orange" onclick="return confirm('Batalkan reservasi ini?')">✗ Batal</a>
                        <?php endif; ?>
                        <a href="?aksi=hapus&id=<?= $r['id'] ?>"
                           class="btn-sm btn-merah" onclick="return confirm('Hapus reservasi ini permanen?')">🗑</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="7" style="text-align:center; color:#999; padding:20px;">Tidak ada data reservasi</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
