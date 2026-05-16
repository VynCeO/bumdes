<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

$pesan = '';
$tipe  = 'success';
$mode  = $_GET['mode'] ?? 'list';

// HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id  = (int)$_GET['id'];
    $row = $conn->query("SELECT foto FROM pimpinan WHERE id=$id")->fetch_assoc();
    if (!empty($row['foto']) && file_exists('../' . $row['foto'])) unlink('../' . $row['foto']);
    $conn->query("DELETE FROM pimpinan WHERE id=$id");
    $pesan = '✅ Data pimpinan berhasil dihapus.';
    $mode  = 'list';
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama'] ?? '');
    $posisi    = trim($_POST['posisi'] ?? '');
    $urutan    = (int)($_POST['urutan'] ?? 1);
    $id        = (int)($_POST['id'] ?? 0);
    $foto_path = $_POST['foto_lama'] ?? '';

    // Proses upload foto
    if (!empty($_FILES['foto']['name'])) {
        $ext_ok = ['jpg','jpeg','png','webp','gif'];
        $ext    = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $ext_ok)) {
            $pesan = '❌ Format foto tidak didukung! Gunakan JPG/PNG/WEBP.';
            $tipe  = 'error';
        } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $pesan = '❌ Ukuran foto maksimal 2MB!';
            $tipe  = 'error';
        } else {
            $dir = '../assets/images/pimpinan/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fname = 'pimpinan_' . time() . '_' . rand(100,999) . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $fname)) {
                $lama = $_POST['foto_lama'] ?? '';
                if ($lama && file_exists('../' . $lama)) unlink('../' . $lama);
                $foto_path = 'assets/images/pimpinan/' . $fname;
            } else {
                $pesan = '❌ Gagal mengupload foto.';
                $tipe  = 'error';
            }
        }
    }

    if (empty($pesan)) {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE pimpinan SET nama=?,posisi=?,urutan=?,foto=? WHERE id=?");
            $stmt->bind_param('ssisi', $nama, $posisi, $urutan, $foto_path, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO pimpinan (nama,posisi,urutan,foto) VALUES (?,?,?,?)");
            $stmt->bind_param('ssis', $nama, $posisi, $urutan, $foto_path);
        }
        if ($stmt->execute()) {
            $pesan = $id > 0 ? '✅ Data pimpinan berhasil diupdate!' : '✅ Data pimpinan berhasil ditambah!';
        } else {
            $pesan = '❌ Terjadi kesalahan!'; $tipe = 'error';
        }
        $mode = 'list';
    }
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
    <div class="alert alert-<?= $tipe ?>" style="margin-bottom:15px;"><?= $pesan ?></div>
    <?php endif; ?>

    <!-- FORM TAMBAH / EDIT -->
    <?php if ($mode === 'tambah' || $mode === 'edit'): ?>
    <div class="form-box" style="margin-bottom:20px;">
        <h2><?= $mode === 'edit' ? '✏ Edit Pimpinan' : '+ Tambah Pimpinan Baru' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($edit_data['foto'] ?? '') ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Posisi / Jabatan *</label>
                <input type="text" name="posisi" value="<?= htmlspecialchars($edit_data['posisi'] ?? '') ?>" placeholder="Contoh: Direktur" required>
            </div>
            <div class="form-group">
                <label>Urutan Tampil</label>
                <input type="number" name="urutan" value="<?= $edit_data['urutan'] ?? 1 ?>" min="1">
            </div>
            <div class="form-group">
                <label>Foto (JPG/PNG/WEBP, maks 2MB)</label>
                <?php if (!empty($edit_data['foto']) && file_exists('../' . $edit_data['foto'])): ?>
                <div style="margin-bottom:8px;">
                    <img src="../<?= htmlspecialchars($edit_data['foto']) ?>" style="height:80px;width:80px;object-fit:cover;border-radius:50%;border:2px solid #1f5b3a;">
                    <small style="display:block;color:#888;margin-top:4px;">Foto saat ini</small>
                </div>
                <?php elseif (!empty($edit_data['nama'])): ?>
                <div style="margin-bottom:8px;">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($edit_data['nama']) ?>&background=1f5b3a&color=fff&size=80"
                         style="height:80px;width:80px;border-radius:50%;">
                    <small style="display:block;color:#888;margin-top:4px;">Menggunakan avatar otomatis</small>
                </div>
                <?php endif; ?>
                <input type="file" name="foto" accept="image/*" style="border:none;padding:0;">
            </div>

            <button type="submit" class="btn-primary"><?= $mode === 'edit' ? 'Update' : 'Tambah' ?></button>
            <a href="manage_pimpinan.php" style="margin-left:10px;color:#888;font-size:14px;">Batal</a>
        </form>
    </div>
    <?php endif; ?>

    <!-- TABEL -->
    <div class="table-box">
        <table>
            <thead><tr><th>No</th><th>Foto</th><th>Nama</th><th>Posisi</th><th>Urutan</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if ($pimpinan_list->num_rows > 0): ?>
            <?php $no = 1; while ($p = $pimpinan_list->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>
                    <?php if (!empty($p['foto']) && file_exists('../' . $p['foto'])): ?>
                    <img src="../<?= htmlspecialchars($p['foto']) ?>" style="width:45px;height:45px;object-fit:cover;border-radius:50%;border:2px solid #1f5b3a;">
                    <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['nama']) ?>&background=1f5b3a&color=fff&size=45"
                         style="width:45px;height:45px;border-radius:50%;">
                    <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                <td><?= htmlspecialchars($p['posisi']) ?></td>
                <td style="text-align:center;"><?= $p['urutan'] ?></td>
                <td>
                    <a href="?mode=edit&id=<?= $p['id'] ?>" class="btn-sm btn-orange">✏ Edit</a>
                    <a href="?aksi=hapus&id=<?= $p['id'] ?>" class="btn-sm btn-merah"
                       onclick="return confirm('Hapus data ini?')">🗑 Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="6" style="text-align:center;color:#999;padding:20px;">Belum ada data</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</body>
</html>
