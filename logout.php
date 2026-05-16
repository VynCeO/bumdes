<?php
session_start();
session_destroy();
// Hapus cookie ingat saya
setcookie('bumdes_user', '', time() - 3600, '/');
header('Location: index.php');
exit;
?>
