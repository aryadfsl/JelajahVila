<?php
include '../DB/koneksi.php';
session_start();

if (isset($_POST['id_pemesanan']) && isset($_POST['from'])) {
    $id = intval($_POST['id_pemesanan']);
    $asal = $_POST['from'];

    // Cek apakah data ada
    $cek = mysqli_query($koneksi, "SELECT * FROM pemesanan WHERE id = $id");

    if (mysqli_num_rows($cek) > 0) {
        if ($asal === 'user') {
            // Soft delete: hanya ubah status jadi 'dihapus_user'
            $update = mysqli_query($koneksi, "UPDATE pemesanan SET status = 'dihapus_user' WHERE id = $id");
            if ($update) {
                $_SESSION['success_message'] = "Data berhasil dihapus.";
                header("Location: ../user/riwayat_pemesan.php?msg=sukses");
                exit;
            } else {
                echo "❌ Gagal menyembunyikan data.";
            }

        } elseif ($asal === 'admin') {
            // Hard delete: hapus permanen
            $hapus = mysqli_query($koneksi, "DELETE FROM pemesanan WHERE id = $id");
            if ($hapus) {
                $_SESSION['success_message'] = "Data berhasil dihapus.";
                header("Location: ../admin/data_pesanan.php?msg=sukses");
                exit;
            } else {
                echo "❌ Gagal menghapus data.";
            }

        } else {
            echo "❌ Asal halaman tidak valid.";
        }

    } else {
        echo "⚠️ Data tidak ditemukan.";
    }
} else {
    echo "⚠️ ID atau asal halaman tidak valid.";
}
?>
