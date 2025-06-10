<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['id_admin'])) {
    header("Location: ../vilago/login.php");
    exit;
}

include '../DB/koneksi.php';

$id_admin = $_SESSION['id_admin'];

// Ambil parameter dari URL
$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);
$vila_id = intval($_GET['vila_id'] ?? 0);

if ($type === 'vila') {
    // Hapus seluruh vila
    if ($id > 0) {
        // Pastikan vila milik admin
        $cek = mysqli_query($koneksi, "SELECT * FROM vila WHERE id = $id AND id_admin = $id_admin");
        if (mysqli_num_rows($cek) > 0) {
            // Hapus semua foto di folder
            $fotoQuery = mysqli_query($koneksi, "SELECT nama_file FROM foto_vila WHERE id_vila = $id");
            while ($foto = mysqli_fetch_assoc($fotoQuery)) {
                $filePath = "../img/" . $foto['nama_file'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            // Hapus data foto dan fasilitas di DB
            mysqli_query($koneksi, "DELETE FROM foto_vila WHERE id_vila = $id");
            mysqli_query($koneksi, "DELETE FROM fasilitas_vila WHERE id_vila = $id");
            // Hapus vila
            mysqli_query($koneksi, "DELETE FROM vila WHERE id = $id AND id_admin = $id_admin");

            echo "<script>alert('Vila dan semua data terkait berhasil dihapus.'); window.location='dashboard.php';</script>";
            exit;
        } else {
            echo "<script>alert('Vila tidak ditemukan atau bukan milik Anda.'); window.location='dashboard.php';</script>";
            exit;
        }
    }
    echo "<script>alert('ID vila tidak valid.'); window.location='dashboard.php';</script>";
    exit;

} elseif ($type === 'foto') {
    // Hapus satu foto vila
    if ($id > 0 && $vila_id > 0) {
        // Pastikan vila milik admin
        $cek = mysqli_query($koneksi, "SELECT * FROM vila WHERE id = $vila_id AND id_admin = $id_admin");
        if (mysqli_num_rows($cek) > 0) {
            // Cari file foto
            $foto = mysqli_query($koneksi, "SELECT nama_file FROM foto_vila WHERE id = $id AND id_vila = $vila_id");
            if (mysqli_num_rows($foto) > 0) {
                $data = mysqli_fetch_assoc($foto);
                $filePath = "../img/" . $data['nama_file'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                // Hapus data foto dari DB
                mysqli_query($koneksi, "DELETE FROM foto_vila WHERE id = $id");
                echo "<script>alert('Foto berhasil dihapus.'); window.location='edit.php?id=$vila_id';</script>";
                exit;
            } else {
                echo "<script>alert('Foto tidak ditemukan.'); window.location='edit.php?id=$vila_id';</script>";
                exit;
            }
        } else {
            echo "<script>alert('Akses tidak diizinkan.'); window.location='dashboard.php';</script>";
            exit;
        }
    }
    echo "<script>alert('Parameter foto tidak valid.'); window.location='dashboard.php';</script>";
    exit;

} elseif ($type === 'fasilitas') {
    // Hapus satu fasilitas vila
    if ($id > 0 && $vila_id > 0) {
        // Pastikan vila milik admin
        $cek = mysqli_query($koneksi, "SELECT * FROM vila WHERE id = $vila_id AND id_admin = $id_admin");
        if (mysqli_num_rows($cek) > 0) {
            // Hapus fasilitas
            mysqli_query($koneksi, "DELETE FROM fasilitas_vila WHERE id = $id AND id_vila = $vila_id");
            echo "<script>alert('Fasilitas berhasil dihapus.'); window.location='edit.php?id=$vila_id';</script>";
            exit;
        } else {
            echo "<script>alert('Akses tidak diizinkan.'); window.location='dashboard.php';</script>";
            exit;
        }
    }
    echo "<script>alert('Parameter fasilitas tidak valid.'); window.location='dashboard.php';</script>";
    exit;

} else {
    // Kalau type tidak valid
    echo "<script>alert('Permintaan tidak valid.'); window.location='dashboard.php';</script>";
    exit;
}
?>