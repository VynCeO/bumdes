<?php
require_once 'config.php';

$pesan = '';
$tipe_pesan = '';

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama'] ?? '');
    $hp      = trim($_POST['no_hp'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $layanan = trim($_POST['layanan'] ?? '');
    $tgl     = $_POST['tanggal'] ?? '';
    $tgl2    = $_POST['tanggal_kembali'] ?? '';
    $ket     = trim($_POST['keterangan'] ?? '');

    if (empty($nama) || empty($hp) || empty($layanan) || empty($tgl)) {
        $pesan = '❌ Mohon lengkapi semua field yang wajib diisi!';
        $tipe_pesan = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO reservasi (nama, no_hp, email, layanan, tanggal, tanggal_kembali, keterangan) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssss', $nama, $hp, $email, $layanan, $tgl, $tgl2, $ket);
        if ($stmt->execute()) {
            $pesan = '✅ Pemesanan berhasil! Kami akan menghubungi Anda segera.';
            $tipe_pesan = 'success';
        } else {
            $pesan = '❌ Terjadi kesalahan. Coba lagi.';
            $tipe_pesan = 'error';
        }
    }
}

// Ambil 8 pemesanan terbaru
$booking_list = $conn->query("SELECT * FROM reservasi ORDER BY created_at DESC LIMIT 8");

// Ambil tanggal yang sudah dipesan
$booked = $conn->query("SELECT tanggal FROM reservasi WHERE status != 'cancelled'");
$booked_dates = [];
while ($b = $booked->fetch_assoc()) {
    $booked_dates[] = $b['tanggal'];
}
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

<!-- LOADER -->
<div id="loader"><div class="spinner"></div></div>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-left">
        <span>🌿</span> BUMDES Sugihwaras
    </div>
    <div class="hamburger" onclick="toggleMenu()">☰</div>
    <div class="nav-right" id="navMenu">
        <a href="index.php">Home</a>
        <div class="dropdown">
            Produk ▾
            <div class="dropdown-menu">
                <a href="produk.php">Semua Produk</a>
                <a href="produk.php?filter=GOR">GOR Sugihwaras</a>
                <a href="produk.php?filter=Tenda">Rental Tenda</a>
            </div>
        </div>
        <a href="reservasi.php">Reservasi</a>
        <a href="index.php#kontak">Kontak</a>
        <a href="admin/login.php" class="btn-masuk">Masuk</a>
    </div>
</nav>

<!-- KONTEN -->
<div class="page-wrapper">
    <div class="box-putih">
        <div class="page-header" style="margin-bottom:10px;">
            <h1>🎫 Reservasi Online</h1>
            <p>Pesan GOR & Tenda dengan mudah</p>
        </div>

        <div class="reservasi-grid">
            <!-- FORM -->
            <div>
                <h3 style="color:#1f5b3a; margin-bottom:15px;">Form Pemesanan</h3>

                <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe_pesan ?>"><?= $pesan ?></div>
                <?php endif; ?>

                <form method="POST" class="form-reservasi">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>

                    <label>No. HP / WhatsApp *</label>
                    <input type="text" name="no_hp" placeholder="Contoh: 08123456789" required>

                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@contoh.com">

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
                    <textarea name="keterangan" rows="3" placeholder="Tambahkan catatan jika ada..."></textarea>

                    <?php if (!empty($booked_dates)): ?>
                    <div style="background:#fff3cd; padding:10px; border-radius:6px; font-size:13px; margin-bottom:10px;">
                        <strong>⚠️ Tanggal sudah dipesan:</strong><br>
                        <?php foreach ($booked_dates as $tgl_busy): ?>
                        <span style="display:inline-block; background:#ffcccc; padding:2px 8px; border-radius:4px; margin:3px 2px; font-size:12px;">
                            <?= date('d/m/Y', strtotime($tgl_busy)) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn-submit">Pesan Sekarang</button>
                </form>
            </div>

            <!-- INFO -->
            <div class="info-panel">
                <h3>📋 Pemesanan Terbaru</h3>
                <?php if ($booking_list->num_rows > 0): ?>
                <?php while ($bk = $booking_list->fetch_assoc()): ?>
                <div class="booking-item">
                    <div class="tgl">
                        📅 <?= date('d/m/Y', strtotime($bk['tanggal'])) ?>
                        <?= $bk['tanggal_kembali'] ? ' s/d ' . date('d/m/Y', strtotime($bk['tanggal_kembali'])) : '' ?>
                    </div>
                    <div class="layanan"><?= htmlspecialchars($bk['layanan']) ?></div>
                    <div style="color:#888; font-size:12px;">Atas nama: <?= htmlspecialchars($bk['nama']) ?></div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <p style="color:#999; text-align:center; margin-top:20px;">Belum ada pemesanan</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <p>© <?= date('Y') ?> BUMDes Sukses Bersama - Desa Sugihwaras</p>
</footer>

<script>
function toggleMenu() {
    document.getElementById('navMenu').classList.toggle('open');
}
window.addEventListener('load', function() {
    document.getElementById('loader').style.display = 'none';
});
</script>
</body>
</html>
