<?php
require_once 'config.php';

$filter = $_GET['filter'] ?? 'ALL';

// Ambil produk
if ($filter === 'ALL') {
    $result = $conn->query("SELECT * FROM unit_usaha WHERE status='aktif' ORDER BY urutan ASC");
} else {
    $stmt = $conn->prepare("SELECT * FROM unit_usaha WHERE status='aktif' AND nama LIKE ? ORDER BY urutan ASC");
    $like = "%$filter%";
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $result = $stmt->get_result();
}

$produk = [];
while ($row = $result->fetch_assoc()) {
    $produk[] = $row;
}

$icons = ['🏟️','⛺','💧','☕','🐄','📋','🏪','🌿','🛒'];
$filters = ['ALL','GOR','Tenda','Air','Kopi','Ternak','PBB'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk & Layanan - BUMDes Sugihwaras</title>
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
                <a href="produk.php?filter=Air">Air Minum</a>
                <a href="produk.php?filter=Kopi">Kopi Melek</a>
                <a href="produk.php?filter=Ternak">Peternakan</a>
                <a href="produk.php?filter=PBB">Pembayaran PBB</a>
            </div>
        </div>
        <a href="reservasi.php">Reservasi</a>
        <a href="index.php#kontak">Kontak</a>
        <a href="admin/login.php" class="btn-masuk">Masuk</a>
    </div>
</nav>

<!-- KONTEN -->
<div class="page-wrapper">
    <div class="page-header">
        <h1>🏪 Produk & Layanan Kami</h1>
        <p>Berbagai pilihan berkualitas untuk kebutuhan Anda</p>
    </div>

    <!-- FILTER -->
    <div class="filter-bar">
        <?php foreach ($filters as $f): ?>
        <a href="produk.php?filter=<?= $f ?>"
           class="filter-btn <?= $filter === $f ? 'active' : '' ?>">
            <?= $f === 'ALL' ? 'Semua Produk' : $f ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- GRID PRODUK -->
    <div class="produk-grid">
        <?php if (empty($produk)): ?>
            <p style="grid-column:1/-1; text-align:center; color:#999; padding:40px;">
                Tidak ada produk untuk kategori ini.
            </p>
        <?php else: ?>
        <?php foreach ($produk as $idx => $p): ?>
        <div class="produk-card">
            <div style="background: linear-gradient(135deg, #1f5b3a, #2d8b5a); height:160px; display:flex; align-items:center; justify-content:center; font-size:64px;">
                <?= $icons[$idx % count($icons)] ?>
            </div>
            <div class="produk-card-body">
                <span class="produk-badge"><?= htmlspecialchars(explode(' ', $p['nama'])[0]) ?></span>
                <h3><?= htmlspecialchars($p['nama']) ?></h3>
                <p><?= htmlspecialchars($p['deskripsi'] ?? 'Produk berkualitas dari BUMDes kami.') ?></p>
                <div class="produk-footer-card">
                    <span class="produk-harga">
                        <?= $p['harga'] > 0 ? 'Rp ' . number_format($p['harga'], 0, ',', '.') : 'Hubungi Kami' ?>
                    </span>
                    <a href="reservasi.php" class="btn-pesan">Pesan</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
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
