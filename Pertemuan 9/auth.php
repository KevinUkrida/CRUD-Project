<?php
// Memulai session (jika belum aktif)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Wajib login: panggil di halaman yang ingin diproteksi
function wajibLogin() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

// Jika sudah login, jangan tampilkan halaman login lagi
function sudahLoginRedirect() {
    if (isset($_SESSION["user_id"])) {
        header("Location: index.php");
        exit;
    }
}
?>