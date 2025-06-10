<?php
include '../DB/koneksi.php';
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit;
}

// Cek apakah parameter yang diperlukan ada
if (!isset($_GET['action']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Parameter tidak valid.";
    header('Location: data_pemesanan.php');
    exit;
}

$action = $_GET['action'];
$id_pemesanan = (int) $_GET['id'];
$id_admin = $_SESSION['id_admin'];

// Validasi action
if (!in_array($action, ['confirm', 'reject'])) {
    $_SESSION['error_message'] = "Aksi tidak valid.";
    header('Location: data_pemesanan.php');
    exit;
}

// Cek apakah pemesanan ada dan milik vila yang dikelola oleh admin
$check_sql = "
    SELECT p.id, p.nama_pemesan, v.nama as nama_vila 
    FROM pemesanan p 
    JOIN vila v ON v.id = p.id_vila 
    WHERE p.id = ? AND v.id_admin = ?
";
$check_stmt = $koneksi->prepare($check_sql);
$check_stmt->bind_param("ii", $id_pemesanan, $id_admin);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $_SESSION['error_message'] = "Pemesanan tidak ditemukan atau Anda tidak memiliki akses untuk mengubahnya.";
    header('Location: data_pesanan.php');
    exit;
}

$booking_data = $check_result->fetch_assoc();

// Tentukan status berdasarkan action
$new_status = '';
$action_text = '';

switch($action) {
    case 'confirm':
        $new_status = 'dikonfirmasi';
        $action_text = 'dikonfirmasi';
        break;
    case 'reject':
        $new_status = 'ditolak';
        $action_text = 'ditolak';
        break;
}

// Update status konfirmasi
$update_sql = "UPDATE pemesanan SET status_konfirmasi = ? WHERE id = ?";
$update_stmt = $koneksi->prepare($update_sql);
$update_stmt->bind_param("si", $new_status, $id_pemesanan);

if ($update_stmt->execute()) {
    $_SESSION['success_message'] = "Pemesanan atas nama " . $booking_data['nama_pemesan'] . 
                                  " untuk vila " . $booking_data['nama_vila'] . 
                                  " berhasil " . $action_text . ".";
} else {
    $_SESSION['error_message'] = "Gagal mengubah status pemesanan. Silakan coba lagi.";
}

// Redirect kembali ke halaman data pemesanan
header('Location: data_pesanan.php');
exit;
?>