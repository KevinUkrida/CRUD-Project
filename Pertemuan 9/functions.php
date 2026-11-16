<?php
function generateNIM($conn, $idProdi, $tahunMasuk) {
    $stmt = $conn->prepare("SELECT kd_prodi FROM program_studi WHERE id_prodi = ?");
    $stmt->bind_param("i", $idProdi);
    $stmt->execute();
    $resultProdi = $stmt->get_result();
    $prodi = $resultProdi->fetch_assoc();

    $kdProdi = $prodi ? $prodi['kd_prodi'] : "42";

    $prefix = $kdProdi . $tahunMasuk;

    $like = $prefix . "%";
    $stmt = $conn->prepare("SELECT nim FROM mahasiswa WHERE nim LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nimTerakhir = $row['nim'];

        $urutan = (int) substr($nimTerakhir, -3) + 1;
    } else {
        $urutan = 1;
    }

    return $prefix . str_pad($urutan, 3, "0", STR_PAD_LEFT);
}

function ambilSemuaMahasiswa($conn) {
    $sql = "SELECT 
                mahasiswa.id,
                mahasiswa.nim,
                mahasiswa.nama,
                mahasiswa.email,
                program_studi.nama_prodi
            FROM mahasiswa
            JOIN program_studi 
              ON mahasiswa.id_prodi = program_studi.id_prodi
            ORDER BY mahasiswa.nama ASC";
    return $conn->query($sql);
}

function ambilSemuaProdi($conn) {
    $sql = "SELECT * FROM program_studi ORDER BY nama_prodi ASC";
    return $conn->query($sql);
}

function tambahMahasiswa($conn, $nim, $nama, $email, $password, $idProdi) {
    $stmt = $conn->prepare(
        "INSERT INTO mahasiswa (nim, nama, email, password, id_prodi) VALUES (?, ?, ?, ?, ?)"
    );

    if (!is_object($stmt)) {
        return false;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt->bind_param("ssssi", $nim, $nama, $email, $hash, $idProdi);
    return $stmt->execute();
}

// data lama otomatis diubah menjadi hash setelah berhasil login.
function loginMahasiswa($conn, $email, $password) {
    $stmt = $conn->prepare("SELECT id, nim, nama, email, password FROM mahasiswa WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        return false;
    }

    $tersimpan = $user["password"];
    $valid = false;

    if (password_get_info($tersimpan)["algo"]) {
        // Password sudah berupa hash
        $valid = password_verify($password, $tersimpan);
    } else {
        // Password lama (teks biasa)
        $valid = hash_equals($tersimpan, $password);
        if ($valid) {
            $hashBaru = password_hash($password, PASSWORD_DEFAULT);
            $up = $conn->prepare("UPDATE mahasiswa SET password = ? WHERE id = ?");
            $up->bind_param("si", $hashBaru, $user["id"]);
            $up->execute();
        }
    }

    return $valid ? $user : false;
}

function ambilMahasiswaById($conn, $id) {
    $stmt = $conn->prepare(
        "SELECT 
            mahasiswa.id,
            mahasiswa.nim,
            mahasiswa.nama,
            mahasiswa.email,
            mahasiswa.id_prodi,
            program_studi.nama_prodi
         FROM mahasiswa
         JOIN program_studi 
           ON mahasiswa.id_prodi = program_studi.id_prodi
         WHERE mahasiswa.id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function updateMahasiswa($conn, $id, $nim, $nama, $email, $idProdi) {
    $stmt = $conn->prepare(
        "UPDATE mahasiswa 
         SET nim = ?, nama = ?, email = ?, id_prodi = ? 
         WHERE id = ?"
    );
    if (!is_object($stmt)) {
        return false;
    }
    $stmt->bind_param("sssii", $nim, $nama, $email, $idProdi, $id);
    return $stmt->execute();
}

function hapusMahasiswa($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE id = ?");
    if (!is_object($stmt)) {
        return false;
    }
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>

