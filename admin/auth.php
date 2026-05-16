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
    echo '<h2><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:inline-block;margin-right:8px;vertical-align:middle;"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2" fill="none"/><rect x="6" y="7" width="4" height="4" fill="currentColor"/><rect x="14" y="7" width="4" height="4" fill="currentColor"/><rect x="6" y="14" width="4" height="3" fill="currentColor"/><rect x="14" y="14" width="4" height="3" fill="currentColor"/></svg>Admin Panel</h2>';
    echo '<p style="font-size:12px;opacity:0.7;margin-bottom:15px;">Halo, '.htmlspecialchars($_SESSION['nama'] ?? 'Admin').'</p>';
    foreach ($menu as $url => $item) {
        $cls  = (basename($_SERVER['PHP_SELF']) === $url || $active === $url) ? 'active' : '';
        $extra = $url === 'logout.php' ? 'logout-link' : '';
        echo "<a href=\"$url\" class=\"$cls $extra\">{$item['icon']} {$item['label']}</a>";
    }
    echo '</div>';
}
