<?php
require_once "config.php";
require_once "functions.php";
require_once "auth.php";

wajibLogin();

$result = ambilSemuaMahasiswa($conn);
$total  = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Mahasiswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">🎓 Pertemuan 9</div>
        <div class="nav-menu">
            <a href="index.php" class="active">Data Mahasiswa</a>
            <a href="tambah_mahasiswa.php">Tambah Mahasiswa</a>
        </div>
        <div class="nav-user">
            <span>Halo, <strong><?= htmlspecialchars($_SESSION["user_nama"]) ?></strong></span>
            <a href="logout.php" class="btn btn-danger btn-sm"
               onclick="return confirm('Yakin ingin logout?')">Logout</a>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <div>
                <h1>Data Mahasiswa</h1>
                <p class="muted">Daftar seluruh mahasiswa yang terdaftar</p>
            </div>
            <a href="tambah_mahasiswa.php" class="btn btn-primary">+ Tambah Mahasiswa</a>
        </div>

        <div class="stat-card">
            <span class="stat-number"><?= $total ?></span>
            <span class="stat-label">Total Mahasiswa</span>
        </div>
        <div class="card">
            <?php if ($total > 0): ?>
                <div class="table-wrap">
                <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Program Studi</th>
                        <th>Aksi</th> <!-- Tambahan Kolom Aksi -->
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no ?></td>
                            <td><span class="badge"><?= htmlspecialchars($row["nim"]) ?></span></td>
                            <td><?= htmlspecialchars($row["nama"]) ?></td>
                            <td><?= htmlspecialchars($row["email"]) ?></td>
                            <td><?= htmlspecialchars($row["nama_prodi"]) ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row["id"] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="hapus.php?id=<?= $row["id"] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php $no++; ?>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty">Belum ada data mahasiswa.</p>
    <?php endif; ?>
</div>
    </main>
</body>
</html>