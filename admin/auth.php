<?php
// Include di semua halaman admin — pastikan session_start() sudah dipanggil

if (!admin_login()) {
    header('Location: login.php');
    exit;
}

function render_sidebar($active = '') {
    $menu = [
        'index.php'            => ['icon'=>'📊','label'=>'Dashboard'],
        'manage_reservasi.php' => ['icon'=>'📅','label'=>'Reservasi'],
        'manage_produk.php'    => ['icon'=>'🏪','label'=>'Produk'],
        'manage_pimpinan.php'  => ['icon'=>'👤','label'=>'Pimpinan'],
        'manage_users.php'     => ['icon'=>'👥','label'=>'Users'],
        'logout.php'           => ['icon'=>'🚪','label'=>'Logout'],
    ];
    echo '<div class="sidebar" id="sidebar">';
    echo '<h2>🌿 Admin Panel</h2>';
    echo '<p style="font-size:12px;opacity:0.7;margin-bottom:15px;">Halo, '.htmlspecialchars($_SESSION['nama'] ?? 'Admin').'</p>';
    foreach ($menu as $url => $item) {
        $cls  = (basename($_SERVER['PHP_SELF']) === $url || $active === $url) ? 'active' : '';
        $extra = $url === 'logout.php' ? 'logout-link' : '';
        echo "<a href=\"$url\" class=\"$cls $extra\">{$item['icon']} {$item['label']}</a>";
    }
    echo '</div>';
}
