<?php
require_once "config.php";
require_once "functions.php";
require_once "auth.php";

wajibLogin();

$nama = "";
$email = "";
$password = "";
$idProdi = "";
$tahunMasuk = date("Y");
$errors = [];

$resultProdi = ambilSemuaProdi($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idProdi    = $_POST["id_prodi"] ?? "";
    $tahunMasuk = trim($_POST["tahun_masuk"] ?? "");
    $nama       = trim($_POST["nama"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $password   = $_POST["password"] ?? "";

    if ($idProdi === "") {
        $errors[] = "Program studi wajib dipilih.";
    }
    if ($tahunMasuk === "") {
        $errors[] = "Tahun masuk wajib diisi.";
    }
    if ($nama === "") {
        $errors[] = "Nama wajib diisi.";
    }
    if ($email === "") {
        $errors[] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    if ($password === "") {
        $errors[] = "Password wajib diisi.";
    }

    if (count($errors) === 0) {
        // Generate NIM otomatis dengan format 3 digit urut di belakang
        $nimOtomatis = generateNIM($conn, $idProdi, $tahunMasuk);

        // Simpan ke database (password otomatis di-hash di dalam functions.php)
        $berhasil = tambahMahasiswa($conn, $nimOtomatis, $nama, $email, $password, (int)$idProdi);

        if ($berhasil) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Data gagal disimpan. Email atau NIM mungkin sudah terdaftar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Mahasiswa | Data Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🎓 Pertemuan 9</div>
        <div class="nav-menu">
            <a href="index.php">Data Mahasiswa</a>
            <a href="tambah_mahasiswa.php" class="active">Tambah Mahasiswa</a>
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
                <h1>Tambah Mahasiswa Baru</h1>
                <p class="muted">NIM akan digenerate secara otomatis oleh sistem</p>
            </div>
            <a href="index.php" class="btn btn-secondary btn-sm">← Kembali</a>
        </div>

        <div class="card card-pad">
            <?php if (count($errors) > 0): ?>
                <div class="alert alert-error">
                    <strong>Terjadi Kesalahan:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="id_prodi">Program Studi</label>
                    <select id="id_prodi" name="id_prodi" required>
                        <option value="">-- Pilih Program Studi --</option>
                        <?php while ($prodi = $resultProdi->fetch_assoc()): ?>
                            <option value="<?= $prodi["id_prodi"] ?>" <?= $idProdi == $prodi["id_prodi"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($prodi["nama_prodi"]) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tahun_masuk">Tahun Masuk</label>
                    <input type="text" id="tahun_masuk" name="tahun_masuk" value="<?= htmlspecialchars($tahunMasuk) ?>" placeholder="Contoh: 2025" required>
                </div>

                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="nama@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password akun" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Simpan Data Mahasiswa</button>
            </form>
        </div>
    </main>
</body>
</html>