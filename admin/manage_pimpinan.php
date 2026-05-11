<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

$pesan = '';
$mode = $_GET['mode'] ?? 'list';

// HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM pimpinan WHERE id=$id");
    $pesan = 'Data pimpinan berhasil dihapus.';
    $mode = 'list';
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama'] ?? '');
    $posisi = trim($_POST['posisi'] ?? '');
    $urutan = (int)($_POST['urutan'] ?? 1);
    $id     = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE pimpinan SET nama=?, posisi=?, urutan=? WHERE id=?");
        $stmt->bind_param('ssii', $nama, $posisi, $urutan, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO pimpinan (nama, posisi, urutan) VALUES (?,?,?)");
        $stmt->bind_param('ssi', $nama, $posisi, $urutan);
    }

    if ($stmt->execute()) {
        $pesan = $id > 0 ? 'Data pimpinan berhasil diupdate!' : 'Data pimpinan berhasil ditambah!';
    } else {
        $pesan = 'Terjadi kesalahan!';
    }
    $mode = 'list';
}

$edit_data = null;
if ($mode === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $edit_data = $conn->query("SELECT * FROM pimpinan WHERE id=$id")->fetch_assoc();
}

$pimpinan_list = $conn->query("SELECT * FROM pimpinan ORDER BY urutan ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pimpinan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">

    <?php render_sidebar('manage_pimpinan.php'); ?>

    <div class="main-content">
        <div class="top-bar">
            <h1>👤 Kelola Pimpinan</h1>
            <a href="?mode=tambah" class="btn-sm btn-hijau" style="padding:8px 16px;">+ Tambah Pimpinan</a>
        </div>

        <?php if ($pesan): ?>
        <div class="alert alert-success" style="margin-bottom:15px;"><?= $pesan ?></div>
        <?php endif; ?>

        <!-- FORM -->
        <?php if ($mode === 'tambah' || $mode === 'edit'): ?>
        <div class="form-box" style="margin-bottom:20px;">
            <h2><?= $mode === 'edit' ? 'Edit Data Pimpinan' : 'Tambah Pimpinan Baru' ?></h2>
            <form method="POST">
                <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Posisi / Jabatan</label>
                    <input type="text" name="posisi" value="<?= htmlspecialchars($edit_data['posisi'] ?? '') ?>" required
                           placeholder="Contoh: Direktur">
                </div>
                <div class="form-group">
                    <label>Urutan Tampil</label>
                    <input type="number" name="urutan" value="<?= $edit_data['urutan'] ?? 1 ?>" min="1">
                </div>

                <button type="submit" class="btn-primary">
                    <?= $mode === 'edit' ? 'Update' : 'Tambah' ?>
                </button>
                <a href="manage_pimpinan.php" style="margin-left:10px; color:#888; font-size:14px;">Batal</a>
            </form>
        </div>
        <?php endif; ?>

        <!-- TABEL -->
        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($pimpinan_list->num_rows > 0): ?>
                <?php while ($p = $pimpinan_list->fetch_assoc()): ?>
                <tr>
                    <td style="text-align:center;"><?= $p['urutan'] ?></td>
                    <td>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['nama']) ?>&background=1f5b3a&color=fff&size=50"
                             style="border-radius:50%; width:45px; height:45px;" alt="">
                    </td>
                    <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                    <td><?= htmlspecialchars($p['posisi']) ?></td>
                    <td>
                        <a href="?mode=edit&id=<?= $p['id'] ?>" class="btn-sm btn-orange">✏ Edit</a>
                        <a href="?aksi=hapus&id=<?= $p['id'] ?>" class="btn-sm btn-merah"
                           onclick="return confirm('Hapus data ini?')">🗑 Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="5" style="text-align:center; color:#999; padding:20px;">Belum ada data pimpinan</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
