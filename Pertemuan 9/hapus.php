<?php
require_once "config.php";
require_once "functions.php";
require_once "auth.php";

wajibLogin();

$id = $_GET['id'] ?? "";

if ($id === "" || !is_numeric($id)) {
    die("ID tidak valid.");
}

$mahasiswa = ambilMahasiswaById($conn, (int) $id);

if (!$mahasiswa) {
    die("Data mahasiswa tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hapusMahasiswa($conn, (int) $id)) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Data gagal dihapus.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hapus Mahasiswa | Data Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🎓 Pertemuan 9</div>
        <div class="nav-menu">
            <a href="index.php">Data Mahasiswa</a>
            <a href="tambah_mahasiswa.php">Tambah Mahasiswa</a>
        </div>
        <div class="nav-user">
            <span>Halo, <strong><?= htmlspecialchars($_SESSION["user_nama"]) ?></strong></span>
            <a href="logout.php" class="btn btn-danger btn-sm"
               onclick="return confirm('Yakin ingin logout?')">Logout</a>
        </div>
    </nav>

    <main class="container container-narrow">
        <div class="page-header">
            <div>
                <h1>Konfirmasi Hapus</h1>
                <p class="muted">Apakah Anda yakin ingin menghapus data mahasiswa ini?</p>
            </div>
            <a href="index.php" class="btn btn-secondary btn-sm">← Batal</a>
        </div>

        <div class="card card-pad">
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <ul style="margin-bottom: 24px; padding-left: 20px; line-height: 1.8;">
                <li><strong>NIM:</strong> <?= htmlspecialchars($mahasiswa['nim']) ?></li>
                <li><strong>Nama:</strong> <?= htmlspecialchars($mahasiswa['nama']) ?></li>
                <li><strong>Email:</strong> <?= htmlspecialchars($mahasiswa['email']) ?></li>
                <li><strong>Program Studi:</strong> <?= htmlspecialchars($mahasiswa['nama_prodi']) ?></li>
            </ul>

            <form method="POST">
                <button type="submit" class="btn btn-danger btn-block">Ya, Hapus Data Ini</button>
            </form>
        </div>
    </main>
</body>
</html>