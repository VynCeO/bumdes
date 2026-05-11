<?php
// File ini di-include di semua halaman admin
// Pastikan sudah session_start() sebelum include ini

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Fungsi render sidebar
function render_sidebar($active = '') {
    $menu = [
        'index.php'              => ['icon'=>'📊', 'label'=>'Dashboard'],
        'manage_reservasi.php'   => ['icon'=>'📅', 'label'=>'Reservasi'],
        'manage_produk.php'      => ['icon'=>'🏪', 'label'=>'Produk'],
        'manage_pimpinan.php'    => ['icon'=>'👤', 'label'=>'Pimpinan'],
        'logout.php'             => ['icon'=>'🚪', 'label'=>'Logout'],
    ];

    echo '<div class="sidebar" id="sidebar">';
    echo '<h2>🌿 Admin Panel</h2>';
    echo '<p style="font-size:12px; opacity:0.7; margin-bottom:15px;">Halo, ' . htmlspecialchars($_SESSION['nama'] ?? 'Admin') . '</p>';

    foreach ($menu as $url => $item) {
        $isActive = (basename($_SERVER['PHP_SELF']) === $url || $active === $url) ? 'active' : '';
        $extraClass = $url === 'logout.php' ? 'logout-link' : '';
        echo "<a href=\"$url\" class=\"$isActive $extraClass\">{$item['icon']} {$item['label']}</a>";
    }

    echo '</div>';
}
?>
