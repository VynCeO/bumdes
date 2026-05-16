<?php
// Konfigurasi database - sesuaikan dengan server Anda
$host   = 'localhost';
$dbname = 'bumdes_db';
$user   = 'root';
$pass   = '';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// Helper: cek login user publik
function user_login() {
    return isset($_SESSION['user_id']);
}

// Helper: cek login admin
function admin_login() {
    return isset($_SESSION['admin_id']);
}

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit;
}
?>
