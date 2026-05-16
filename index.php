<?php
session_start();
require_once 'config.php';

$pimpinan  = $conn->query("SELECT * FROM pimpinan ORDER BY urutan ASC");
$unit_usaha = $conn->query("SELECT * FROM unit_usaha WHERE status='aktif' ORDER BY urutan ASC");
$icons = ['🏟️','⛺','💧','☕','🐄','📋'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUMDes Sukses Bersama - Desa Sugihwaras</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div id="loader"><div class="spinner"></div></div>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-left">🌿 BUMDES Sugihwaras</div>
    <div class="hamburger" onclick="toggleMenu()">☰</div>
    <div class="nav-right" id="navMenu">
        <a href="index.php">Home</a>
        <div class="dropdown">Produk ▾
            <div class="dropdown-menu">
                <a href="produk.php">Semua Produk</a>
                <a href="produk.php?filter=GOR">GOR Sugihwaras</a>
                <a href="produk.php?filter=Tenda">Rental Tenda</a>
                <a href="produk.php?filter=Air">Air Minum</a>
                <a href="produk.php?filter=Kopi">Kopi Melek</a>
                <a href="produk.php?filter=Ternak">Peternakan</a>
                <a href="produk.php?filter=PBB">Pembayaran PBB</a>
            </div>
        </div>
        <a href="reservasi.php">Reservasi</a>
        <a href="#kontak">Kontak</a>

        <?php if (user_login()): ?>
        <div class="dropdown">
            👤 <?= htmlspecialchars(explode(' ', $_SESSION['user_nama'])[0]) ?> ▾
            <div class="dropdown-menu">
                <a href="reservasi.php">📅 Reservasi Saya</a>
                <a href="logout.php">🚪 Logout</a>
            </div>
        </div>
        <?php else: ?>
        <a href="login.php" class="btn-masuk">Masuk</a>
        <?php endif; ?>

        <a href="admin/login.php" style="font-size:12px;color:#999;">Admin</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero" id="home">
    <div class="hero-text">
        <p class="hero-kecil">Selamat Datang di</p>
        <h1>BUMDes Sukses Bersama</h1>
        <p class="hero-kecil">Desa Sugihwaras</p>
        <a href="#unit-usaha" class="hero-btn">Jelajahi Layanan</a>
    </div>
</section>

<!-- PROFIL PIMPINAN -->
<section class="pimpinan reveal" id="pimpinan">
    <h2 class="section-title">Profil Pimpinan</h2>
    <div class="pimpinan-container">
        <?php while ($p = $pimpinan->fetch_assoc()): ?>
        <div class="card-pimpinan">
            <?php if (!empty($p['foto']) && file_exists($p['foto'])): ?>
                <img src="<?= htmlspecialchars($p['foto']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>">
            <?php else: ?>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['nama']) ?>&background=1f5b3a&color=fff&size=200" alt="<?= htmlspecialchars($p['nama']) ?>">
            <?php endif; ?>
            <div class="card-info">
                <h3><?= htmlspecialchars($p['nama']) ?></h3>
                <p><?= htmlspecialchars($p['posisi']) ?></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- UNIT USAHA -->
<section class="unit-usaha reveal" id="unit-usaha">
    <h2 class="section-title">Unit Usaha Kami</h2>
    <div class="unit-container">
        <?php $i = 0; $unit_usaha->data_seek(0); while ($u = $unit_usaha->fetch_assoc()): ?>
        <a href="produk.php?filter=<?= urlencode($u['nama']) ?>" class="unit-card">
            <div class="icon"><?= $icons[$i % count($icons)] ?></div>
            <p><?= htmlspecialchars($u['nama']) ?></p>
        </a>
        <?php $i++; endwhile; ?>
    </div>
</section>

<!-- KONTAK -->
<section class="kontak reveal" id="kontak">
    <div class="kontak-wrapper">
        <div class="kontak-kiri">
            <h2>Kontak Kami</h2>
            <p>📍 Jl. H. Nur Sugihwaras, RT 11/RW 03, Rejo, Candi, Sidoarjo</p>
            <p>📞 Telp: 0877-5813-5806</p>
            <p>💬 WhatsApp: 0877-5813-5806</p>
            <p>📧 bumdes@sugihwaras.id</p>
        </div>
        <div class="kontak-kanan">
            <a href="https://wa.me/6287758135806" target="_blank" class="sosmed-link">
                <span class="sosmed-icon">💬</span> WhatsApp
            </a>
            <a href="https://www.instagram.com/bumdes.sugihwaras19" target="_blank" class="sosmed-link">
                <span class="sosmed-icon">📷</span> Instagram
            </a>
            <a href="https://www.facebook.com" target="_blank" class="sosmed-link">
                <span class="sosmed-icon">👍</span> Facebook
            </a>
        </div>
    </div>
</section>

<footer><p>© <?= date('Y') ?> BUMDes Sukses Bersama - Desa Sugihwaras</p></footer>

<script>
function toggleMenu() { document.getElementById('navMenu').classList.toggle('open'); }
function checkReveal() {
    document.querySelectorAll('.reveal').forEach(function(el) {
        if (el.getBoundingClientRect().top < window.innerHeight - 100) el.classList.add('active');
    });
}
window.addEventListener('scroll', checkReveal);
window.addEventListener('load', function() {
    document.getElementById('loader').style.display = 'none';
    checkReveal();
});
</script>
</body>
</html>
