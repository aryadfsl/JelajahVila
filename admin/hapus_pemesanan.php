<?php
include '../DB/koneksi.php'; // Sesuaikan jika koneksi.php di luar folder /admin/

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Cek apakah data dengan ID tersebut ada
    $cek = mysqli_query($koneksi, "SELECT * FROM pemesanan WHERE id = $id");

    if (mysqli_num_rows($cek) > 0) {
        // Hapus data dari database
        $hapus = mysqli_query($koneksi, "DELETE FROM pemesanan WHERE id = $id");

        if ($hapus) {
            // Redirect ke halaman data dengan pesan sukses
            header("Location: data_pesanan.php?msg=sukses");
            exit;
        } else {
            echo "❌ Gagal menghapus data. Silakan coba lagi.";
        }
    } else {
        echo "⚠️ Data dengan ID tersebut tidak ditemukan.";
    }
} else {
    echo "⚠️ ID tidak valid atau tidak dikirimkan.";
}
?>
