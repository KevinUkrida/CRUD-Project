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

// Data lama
$nimLama     = $mahasiswa["nim"];
$idProdiLama = $mahasiswa["id_prodi"];
$tahunLama   = substr($nimLama, -7, 4);

// Nilai awal form = data lama
$nama       = $mahasiswa["nama"];
$email      = $mahasiswa["email"];
$idProdi    = $idProdiLama;
$tahunMasuk = $tahunLama;
$errors     = [];

$resultProdi = ambilSemuaProdi($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama       = trim($_POST["nama"] ?? "");
    $email      = trim($_POST["email"] ?? "");
    $idProdi    = $_POST["id_prodi"] ?? "";
    $tahunMasuk = trim($_POST["tahun_masuk"] ?? "");

    if ($nama === "") {
        $errors[] = "Nama wajib diisi.";
    }
    if ($email === "") {
        $errors[] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    if ($idProdi === "") {
        $errors[] = "Program studi wajib dipilih.";
    }
    if (!preg_match('/^\d{4}$/', $tahunMasuk)) {
        $errors[] = "Tahun masuk harus 4 digit angka.";
    }

    if (count($errors) === 0) {
        // NIM hanya dibuat ulang jika prodi atau tahun masuk berubah
        if ($idProdi != $idProdiLama || $tahunMasuk != $tahunLama) {
            $nim = generateNIM($conn, (int) $idProdi, $tahunMasuk);
        } else {
            $nim = $nimLama;
        }

        $berhasil = updateMahasiswa($conn, (int) $id, $nim, $nama, $email, (int) $idProdi);

        if ($berhasil) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Data gagal diperbarui. Email atau NIM mungkin sudah digunakan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Mahasiswa | Data Mahasiswa</title>
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
                <h1>Edit Data Mahasiswa</h1>
                <p class="muted">NIM akan berubah otomatis jika program studi atau tahun masuk diubah</p>
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
                    <label for="nim">NIM</label>
                    <input type="text" id="nim" value="<?= htmlspecialchars($nimLama) ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>

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
                    <input type="text" id="tahun_masuk" name="tahun_masuk"
                           value="<?= htmlspecialchars($tahunMasuk) ?>" placeholder="" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
            </form>
        </div>
    </main>
</body>
</html>