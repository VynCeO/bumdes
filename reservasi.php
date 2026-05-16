<?php
session_start();
require_once 'config.php';

// Wajib login untuk reservasi
if (!user_login()) {
    redirect('login.php');
}

$pesan = '';
$tipe_pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $nama    = trim($_POST['nama'] ?? '');
    $hp      = trim($_POST['no_hp'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $layanan = trim($_POST['layanan'] ?? '');
    $tgl     = $_POST['tanggal'] ?? '';
    $tgl2    = $_POST['tanggal_kembali'] ?? '';
    $ket     = trim($_POST['keterangan'] ?? '');

    if (empty($nama) || empty($hp) || empty($layanan) || empty($tgl)) {
        $pesan = '❌ Mohon lengkapi semua field wajib!';
        $tipe_pesan = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO reservasi (user_id,nama,no_hp,email,layanan,tanggal,tanggal_kembali,keterangan) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('isssssss', $user_id, $nama, $hp, $email, $layanan, $tgl, $tgl2, $ket);
        if ($stmt->execute()) {
            $pesan = '✅ Pemesanan berhasil! Kami akan menghubungi Anda segera.';
            $tipe_pesan = 'success';
        } else {
            $pesan = '❌ Terjadi kesalahan. Coba lagi.';
            $tipe_pesan = 'error';
        }
    }
}

// Reservasi MILIK USER ini saja
$my_reservasi = $conn->prepare("SELECT * FROM reservasi WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
$my_reservasi->bind_param('i', $_SESSION['user_id']);
$my_reservasi->execute();
$my_list = $my_reservasi->get_result();

// Tanggal yang sudah terpesan (semua user)
$booked = $conn->query("SELECT tanggal FROM reservasi WHERE status != 'cancelled'");
$booked_dates = [];
while ($b = $booked->fetch_assoc()) $booked_dates[] = $b['tanggal'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi - BUMDes Sugihwaras</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div id="loader"><div class="spinner"></div></div>

<nav class="navbar">
    <div class="nav-left">🌿 BUMDES Sugihwaras</div>
    <div class="hamburger" onclick="toggleMenu()">☰</div>
    <div class="nav-right" id="navMenu">
        <a href="index.php">Home</a>
        <a href="produk.php">Produk</a>
        <a href="reservasi.php">Reservasi</a>
        <a href="index.php#kontak">Kontak</a>
        <div class="dropdown">
            👤 <?= htmlspecialchars(explode(' ', $_SESSION['user_nama'])[0]) ?> ▾
            <div class="dropdown-menu">
                <a href="reservasi.php">📅 Reservasi Saya</a>
                <a href="logout.php" style="color:#dc3545;">🚪 Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="page-wrapper">
    <div class="box-putih">
        <div class="page-header" style="margin-bottom:10px;">
            <h1>🎫 Reservasi Online</h1>
            <p>Halo, <strong><?= htmlspecialchars($_SESSION['user_nama']) ?></strong> — silakan isi form di bawah</p>
        </div>

        <div class="reservasi-grid">
            <!-- FORM -->
            <div>
                <h3 style="color:#1f5b3a;margin-bottom:15px;">Form Pemesanan</h3>

                <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe_pesan ?>"><?= $pesan ?></div>
                <?php endif; ?>

                <form method="POST" class="form-reservasi">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($_SESSION['user_nama']) ?>" required>

                    <label>No. HP / WhatsApp *</label>
                    <input type="text" name="no_hp" value="<?= htmlspecialchars($_SESSION['user_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx" required>

                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['user_email']) ?>">

                    <label>Pilih Layanan *</label>
                    <select name="layanan" required>
                        <option value="">-- Pilih Layanan --</option>
                        <option value="Sewa GOR">Sewa GOR</option>
                        <option value="Rental Tenda">Rental Tenda</option>
                    </select>

                    <label>Tanggal Mulai *</label>
                    <input type="date" name="tanggal" required min="<?= date('Y-m-d') ?>">

                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_kembali" min="<?= date('Y-m-d') ?>">

                    <label>Keterangan</label>
                    <textarea name="keterangan" rows="3" placeholder="Catatan tambahan..."></textarea>

                    <?php if (!empty($booked_dates)): ?>
                    <div style="background:#fff3cd;padding:10px;border-radius:6px;font-size:13px;margin-bottom:12px;">
                        <strong>⚠️ Tanggal sudah dipesan:</strong><br>
                        <?php foreach ($booked_dates as $tgl_busy): ?>
                        <span style="display:inline-block;background:#ffcccc;padding:2px 8px;border-radius:4px;margin:3px 2px;font-size:12px;">
                            <?= date('d/m/Y', strtotime($tgl_busy)) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn-submit">Pesan Sekarang</button>
                </form>
            </div>

            <!-- RIWAYAT RESERVASI USER -->
            <div class="info-panel">
                <h3>📋 Riwayat Reservasi Saya</h3>
                <?php if ($my_list->num_rows > 0): ?>
                <?php while ($r = $my_list->fetch_assoc()): ?>
                <div class="booking-item">
                    <div class="tgl">📅 <?= date('d/m/Y', strtotime($r['tanggal'])) ?>
                        <?= $r['tanggal_kembali'] ? ' s/d '.date('d/m/Y',strtotime($r['tanggal_kembali'])) : '' ?>
                    </div>
                    <div class="layanan"><?= htmlspecialchars($r['layanan']) ?></div>
                    <div style="margin-top:4px;">
                        <?php
                        $badge_map = ['pending'=>'badge-pending','confirmed'=>'badge-confirmed','cancelled'=>'badge-cancelled'];
                        $badge = $badge_map[$r['status']] ?? 'badge-pending';
                        ?>
                        <span class="badge <?= $badge ?>"><?= ucfirst($r['status']) ?></span>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <p style="color:#999;text-align:center;margin-top:20px;font-size:13px;">Belum ada reservasi</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer><p>© <?= date('Y') ?> BUMDes Sukses Bersama - Desa Sugihwaras</p></footer>

<script>
function toggleMenu() { document.getElementById('navMenu').classList.toggle('open'); }
window.addEventListener('load', function() { document.getElementById('loader').style.display = 'none'; });
</script>
</body>
</html>
