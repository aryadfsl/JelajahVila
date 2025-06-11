<?php
include '../DB/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_vila = isset($_POST['id_vila']) ? intval($_POST['id_vila']) : 0;
    $nama    = isset($_POST['nama']) ? mysqli_real_escape_string($koneksi, $_POST['nama']) : '';
    $email   = isset($_POST['email']) ? mysqli_real_escape_string($koneksi, $_POST['email']) : '';
    $pesan   = isset($_POST['pesan']) ? mysqli_real_escape_string($koneksi, $_POST['pesan']) : '';
    $rating  = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

    // Validasi ID vila
    if ($id_vila <= 0) {
        echo "ID vila tidak valid.";
        exit;
    }

    // Validasi rating
    if ($rating < 1 || $rating > 5) {
        echo "Rating tidak valid.";
        exit;
    }

    // Validasi input lain jika perlu (nama, email, pesan tidak kosong)
    if (empty($nama) || empty($email) || empty($pesan)) {
        echo "Mohon lengkapi semua data.";
        exit;
    }

    // Insert komentar ke database
    $query = "INSERT INTO komentar (id_vila, nama, email, pesan, rating, tanggal) 
              VALUES ($id_vila, '$nama', '$email', '$pesan', $rating, NOW())";

    if (mysqli_query($koneksi, $query)) {
        // Redirect ke detail dengan pesan sukses
        header("Location:../vilago/detail.php?id=$id_vila&status=success");
        exit;
    } else {
        echo "Gagal menyimpan komentar: " . mysqli_error($koneksi);
    }
} else {
    echo "Akses tidak sah.";
}
?>
