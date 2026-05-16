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
    $row = $conn->query("SELECT foto FROM unit_usaha WHERE id=$id")->fetch_assoc();
    if (!empty($row['foto']) && file_exists('../' . $row['foto'])) unlink('../' . $row['foto']);
    $conn->query("DELETE FROM unit_usaha WHERE id=$id");
    $pesan = '✅ Produk berhasil dihapus.';
    $mode  = 'list';
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga     = (float)($_POST['harga'] ?? 0);
    $status    = $_POST['status'] ?? 'aktif';
    $id        = (int)($_POST['id'] ?? 0);
    $foto_path = $_POST['foto_lama'] ?? '';

    // Proses upload foto
    if (!empty($_FILES['foto']['name'])) {
        $ext_ok = ['jpg','jpeg','png','webp','gif'];
        $ext    = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $ext_ok)) {
            $pesan = '❌ Format foto tidak didukung! Gunakan JPG/PNG/WEBP.';
            $tipe  = 'error';
        } elseif ($_FILES['foto']['size'] > 3 * 1024 * 1024) {
            $pesan = '❌ Ukuran foto maksimal 3MB!';
            $tipe  = 'error';
        } else {
            $dir = '../assets/images/produk/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $fname = 'produk_' . time() . '_' . rand(100,999) . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . $fname)) {
                $lama = $_POST['foto_lama'] ?? '';
                if ($lama && file_exists('../' . $lama)) unlink('../' . $lama);
                $foto_path = 'assets/images/produk/' . $fname;
            } else {
                $pesan = '❌ Gagal mengupload foto.';
                $tipe  = 'error';
            }
        }
    }

    if (empty($pesan)) {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE unit_usaha SET nama=?,deskripsi=?,harga=?,status=?,foto=? WHERE id=?");
            $stmt->bind_param('ssdssi', $nama, $deskripsi, $harga, $status, $foto_path, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO unit_usaha (nama,deskripsi,harga,status,foto) VALUES (?,?,?,?,?)");
            $stmt->bind_param('ssdss', $nama, $deskripsi, $harga, $status, $foto_path);
        }
        if ($stmt->execute()) {
            $pesan = $id > 0 ? '✅ Produk berhasil diupdate!' : '✅ Produk berhasil ditambah!';
        } else {
            $pesan = '❌ Terjadi kesalahan!'; $tipe = 'error';
        }
        $mode = 'list';
    }
}

$edit_data = null;
if ($mode === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $edit_data = $conn->query("SELECT * FROM unit_usaha WHERE id=$id")->fetch_assoc();
}

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
    <div class="alert alert-<?= $tipe ?>" style="margin-bottom:15px;"><?= $pesan ?></div>
    <?php endif; ?>

    <!-- FORM TAMBAH / EDIT -->
    <?php if ($mode === 'tambah' || $mode === 'edit'): ?>
    <div class="form-box" style="margin-bottom:20px;">
        <h2><?= $mode === 'edit' ? '✏ Edit Produk' : '+ Tambah Produk Baru' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($edit_data['foto'] ?? '') ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Nama Produk *</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="3"><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Harga (Rp) — isi 0 jika "Hubungi Kami"</label>
                <input type="number" name="harga" value="<?= $edit_data['harga'] ?? 0 ?>" min="0">
            </div>
            <div class="form-group">
                <label>Foto Produk (JPG/PNG/WEBP, maks 3MB)</label>
                <?php if (!empty($edit_data['foto']) && file_exists('../' . $edit_data['foto'])): ?>
                <div style="margin-bottom:8px;">
                    <img src="../<?= htmlspecialchars($edit_data['foto']) ?>" style="height:80px;border-radius:6px;border:1px solid #ddd;">
                    <small style="display:block;color:#888;margin-top:4px;">Foto saat ini</small>
                </div>
                <?php endif; ?>
                <input type="file" name="foto" accept="image/*" style="border:none;padding:0;">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="aktif"    <?= ($edit_data['status'] ?? '') === 'aktif'    ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= ($edit_data['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>

            <button type="submit" class="btn-primary"><?= $mode === 'edit' ? 'Update Produk' : 'Tambah Produk' ?></button>
            <a href="manage_produk.php" style="margin-left:10px;color:#888;font-size:14px;">Batal</a>
        </form>
    </div>
    <?php endif; ?>

    <!-- TABEL -->
    <div class="table-box">
        <table>
            <thead><tr><th>#</th><th>Foto</th><th>Nama Produk</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if ($produk_list->num_rows > 0): ?>
            <?php $no = 1; while ($p = $produk_list->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>
                    <?php if (!empty($p['foto']) && file_exists('../' . $p['foto'])): ?>
                    <img src="../<?= htmlspecialchars($p['foto']) ?>" style="width:50px;height:40px;object-fit:cover;border-radius:4px;">
                    <?php else: ?>
                    <span style="font-size:28px;">🏪</span>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($p['nama']) ?></strong><br>
                    <small style="color:#888;"><?= htmlspecialchars(substr($p['deskripsi'] ?? '', 0, 60)) ?>...</small>
                </td>
                <td><?= $p['harga'] > 0 ? 'Rp '.number_format($p['harga'],0,',','.') : 'Hubungi Kami' ?></td>
                <td><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                <td>
                    <a href="?mode=edit&id=<?= $p['id'] ?>" class="btn-sm btn-orange">✏ Edit</a>
                    <a href="?aksi=hapus&id=<?= $p['id'] ?>" class="btn-sm btn-merah"
                       onclick="return confirm('Hapus produk ini?')">🗑 Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="6" style="text-align:center;color:#999;padding:20px;">Belum ada produk</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</body>
</html>
