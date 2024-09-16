<?php
session_start();

// Menghapus semua sesi
session_unset();
session_destroy();

// Redirect ke halaman login atau halaman utama setelah logout
header("Location: index.php");
exit;
?>
