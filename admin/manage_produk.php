<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

$pesan = '';
$mode = $_GET['mode'] ?? 'list'; // list | tambah | edit

// HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM unit_usaha WHERE id=$id");
    $pesan = 'Produk berhasil dihapus.';
    $mode = 'list';
}

// SIMPAN (tambah / edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga   = (float)($_POST['harga'] ?? 0);
    $status  = $_POST['status'] ?? 'aktif';
    $id      = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE unit_usaha SET nama=?, deskripsi=?, harga=?, status=? WHERE id=?");
        $stmt->bind_param('ssdsi', $nama, $deskripsi, $harga, $status, $id);
    } else {
        // Tambah
        $stmt = $conn->prepare("INSERT INTO unit_usaha (nama, deskripsi, harga, status) VALUES (?,?,?,?)");
        $stmt->bind_param('ssds', $nama, $deskripsi, $harga, $status);
    }

    if ($stmt->execute()) {
        $pesan = $id > 0 ? 'Produk berhasil diupdate!' : 'Produk berhasil ditambah!';
    } else {
        $pesan = 'Terjadi kesalahan!';
    }
    $mode = 'list';
}

// Data untuk edit
$edit_data = null;
if ($mode === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $edit_data = $conn->query("SELECT * FROM unit_usaha WHERE id=$id")->fetch_assoc();
}

// List produk
$produk_list = $conn->query("SELECT * FROM unit_usaha ORDER BY urutan ASC, id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">

    <?php render_sidebar('manage_produk.php'); ?>

    <div class="main-content">
        <div class="top-bar">
            <h1>🏪 Kelola Produk</h1>
            <a href="?mode=tambah" class="btn-sm btn-hijau" style="padding:8px 16px;">+ Tambah Produk</a>
        </div>

        <?php if ($pesan): ?>
        <div class="alert alert-success" style="margin-bottom:15px;"><?= $pesan ?></div>
        <?php endif; ?>

        <!-- FORM TAMBAH / EDIT -->
        <?php if ($mode === 'tambah' || $mode === 'edit'): ?>
        <div class="form-box" style="margin-bottom:20px;">
            <h2><?= $mode === 'edit' ? 'Edit Produk' : 'Tambah Produk Baru' ?></h2>
            <form method="POST">
                <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="3"><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Harga (Rp) - isi 0 jika "Hubungi Kami"</label>
                    <input type="number" name="harga" value="<?= $edit_data['harga'] ?? 0 ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="aktif" <?= ($edit_data['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= ($edit_data['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">
                    <?= $mode === 'edit' ? 'Update Produk' : 'Tambah Produk' ?>
                </button>
                <a href="manage_produk.php" style="margin-left:10px; color:#888; font-size:14px;">Batal</a>
            </form>
        </div>
        <?php endif; ?>

        <!-- TABEL PRODUK -->
        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Produk</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($produk_list->num_rows > 0): ?>
                <?php $no = 1; while ($p = $produk_list->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                    <td style="max-width:250px; font-size:13px; color:#666;">
                        <?= htmlspecialchars(substr($p['deskripsi'] ?? '', 0, 80)) ?>...
                    </td>
                    <td>
                        <?= $p['harga'] > 0 ? 'Rp ' . number_format($p['harga'], 0, ',', '.') : 'Hubungi Kami' ?>
                    </td>
                    <td><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td>
                        <a href="?mode=edit&id=<?= $p['id'] ?>" class="btn-sm btn-orange">✏ Edit</a>
                        <a href="?aksi=hapus&id=<?= $p['id'] ?>" class="btn-sm btn-merah"
                           onclick="return confirm('Hapus produk ini?')">🗑 Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="6" style="text-align:center; color:#999; padding:20px;">Belum ada produk</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
