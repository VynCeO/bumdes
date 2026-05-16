<?php
session_start();
require_once '../config.php';
require_once 'auth.php';

$pesan = '';

// --- TAMBAH ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $nama  = trim($_POST['nama'] ?? '');
    $hp    = trim($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $layan = trim($_POST['layanan'] ?? '');
    $tgl   = $_POST['tanggal'] ?? '';
    $tgl2  = $_POST['tanggal_kembali'] ?? '';
    $ket   = trim($_POST['keterangan'] ?? '');
    $status= $_POST['status'] ?? 'pending';

    if ($nama && $hp && $layan && $tgl) {
        $stmt = $conn->prepare("INSERT INTO reservasi (nama,no_hp,email,layanan,tanggal,tanggal_kembali,keterangan,status) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssssss', $nama, $hp, $email, $layan, $tgl, $tgl2, $ket, $status);
        $stmt->execute();
        $pesan = '✅ Reservasi berhasil ditambahkan!';
    } else {
        $pesan = '❌ Nama, HP, Layanan, dan Tanggal wajib diisi!';
    }
}

// --- EDIT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'edit') {
    $id    = (int)$_POST['id'];
    $nama  = trim($_POST['nama'] ?? '');
    $hp    = trim($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $layan = trim($_POST['layanan'] ?? '');
    $tgl   = $_POST['tanggal'] ?? '';
    $tgl2  = $_POST['tanggal_kembali'] ?? '';
    $ket   = trim($_POST['keterangan'] ?? '');
    $status= $_POST['status'] ?? 'pending';

    $stmt = $conn->prepare("UPDATE reservasi SET nama=?,no_hp=?,email=?,layanan=?,tanggal=?,tanggal_kembali=?,keterangan=?,status=? WHERE id=?");
    $stmt->bind_param('ssssssssi', $nama, $hp, $email, $layan, $tgl, $tgl2, $ket, $status, $id);
    $stmt->execute();
    $pesan = '✅ Reservasi berhasil diupdate!';
}

// --- HAPUS ---
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM reservasi WHERE id=$id");
    $pesan = '✅ Reservasi berhasil dihapus.';
}

// --- UPDATE STATUS CEPAT ---
if (isset($_GET['aksi']) && $_GET['aksi'] === 'status' && isset($_GET['id']) && isset($_GET['val'])) {
    $id  = (int)$_GET['id'];
    $val = $_GET['val'];
    if (in_array($val, ['pending','confirmed','cancelled'])) {
        $conn->query("UPDATE reservasi SET status='$val' WHERE id=$id");
        $pesan = "✅ Status diubah ke '$val'.";
    }
}

$mode = $_GET['mode'] ?? 'list';
$filter_status = $_GET['status'] ?? 'ALL';

// Data untuk edit
$edit_data = null;
if ($mode === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $edit_data = $conn->query("SELECT * FROM reservasi WHERE id=$id")->fetch_assoc();
}

// List
if ($filter_status !== 'ALL') {
    $stmt = $conn->prepare("SELECT r.*, u.nama as user_nama FROM reservasi r LEFT JOIN users u ON r.user_id=u.id WHERE r.status=? ORDER BY r.created_at DESC");
    $stmt->bind_param('s', $filter_status);
    $stmt->execute();
    $list = $stmt->get_result();
} else {
    $list = $conn->query("SELECT r.*, u.nama as user_nama FROM reservasi r LEFT JOIN users u ON r.user_id=u.id ORDER BY r.created_at DESC");
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
        <a href="?mode=tambah" class="btn-sm btn-hijau" style="padding:8px 16px;">+ Tambah Reservasi</a>
    </div>

    <?php if ($pesan): ?>
    <div class="alert alert-success" style="margin-bottom:15px;"><?= $pesan ?></div>
    <?php endif; ?>

    <!-- FORM TAMBAH / EDIT -->
    <?php if ($mode === 'tambah' || $mode === 'edit'): ?>
    <div class="form-box" style="margin-bottom:20px;">
        <h2><?= $mode === 'edit' ? '✏ Edit Reservasi' : '+ Tambah Reservasi Baru' ?></h2>
        <form method="POST">
            <input type="hidden" name="aksi" value="<?= $mode ?>">
            <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="form-group">
                    <label>Nama *</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>No. HP *</label>
                    <input type="text" name="no_hp" value="<?= htmlspecialchars($edit_data['no_hp'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($edit_data['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Layanan *</label>
                    <select name="layanan" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach (['Sewa GOR','Rental Tenda'] as $l): ?>
                        <option value="<?= $l ?>" <?= ($edit_data['layanan'] ?? '') === $l ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Mulai *</label>
                    <input type="date" name="tanggal" value="<?= $edit_data['tanggal'] ?? '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_kembali" value="<?= $edit_data['tanggal_kembali'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['pending','confirmed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($edit_data['status'] ?? 'pending') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <input type="text" name="keterangan" value="<?= htmlspecialchars($edit_data['keterangan'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn-primary"><?= $mode === 'edit' ? 'Update' : 'Tambah' ?></button>
            <a href="manage_reservasi.php" style="margin-left:10px;color:#888;font-size:14px;">Batal</a>
        </form>
    </div>
    <?php endif; ?>

    <!-- FILTER STATUS -->
    <div style="margin-bottom:14px;display:flex;gap:8px;flex-wrap:wrap;">
        <?php foreach (['ALL','pending','confirmed','cancelled'] as $s): ?>
        <a href="?status=<?= $s ?>" class="btn-sm <?= $filter_status === $s ? 'btn-hijau' : 'btn-orange' ?>" style="padding:7px 14px;">
            <?= $s === 'ALL' ? 'Semua' : ucfirst($s) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- TABEL -->
    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Nama</th><th>Layanan</th><th>Tanggal</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($list && $list->num_rows > 0): ?>
            <?php $no = 1; while ($r = $list->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>
                    <strong><?= htmlspecialchars($r['nama']) ?></strong><br>
                    <small style="color:#888;"><?= htmlspecialchars($r['no_hp']) ?></small>
                    <?php if ($r['user_nama']): ?>
                    <br><small style="color:#aaa;">via: <?= htmlspecialchars($r['user_nama']) ?></small>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($r['layanan']) ?></td>
                <td>
                    <?= date('d/m/Y', strtotime($r['tanggal'])) ?>
                    <?php if ($r['tanggal_kembali']): ?><br><small>s/d <?= date('d/m/Y',strtotime($r['tanggal_kembali'])) ?></small><?php endif; ?>
                </td>
                <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                <td style="white-space:nowrap;">
                    <a href="?mode=edit&id=<?= $r['id'] ?>" class="btn-sm btn-orange">✏ Edit</a>
                    <?php if ($r['status'] === 'pending'): ?>
                    <a href="?aksi=status&id=<?= $r['id'] ?>&val=confirmed" class="btn-sm btn-hijau"
                       onclick="return confirm('Konfirmasi?')">✓</a>
                    <?php endif; ?>
                    <?php if ($r['status'] !== 'cancelled'): ?>
                    <a href="?aksi=status&id=<?= $r['id'] ?>&val=cancelled" class="btn-sm"
                       style="background:#f0ad4e;color:white;" onclick="return confirm('Batalkan?')">✗</a>
                    <?php endif; ?>
                    <a href="?aksi=hapus&id=<?= $r['id'] ?>" class="btn-sm btn-merah"
                       onclick="return confirm('Hapus permanen?')">🗑</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="6" style="text-align:center;color:#999;padding:20px;">Tidak ada data</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</body>
</html>
