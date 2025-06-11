<?php
include '../DB/koneksi.php';
session_start();
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Pastikan admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit;
}

// Validasi POST
if (!isset($_POST['id_pemesanan'], $_POST['aksi']) || !is_numeric($_POST['id_pemesanan'])) {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
    header('Location: data_pesanan.php');
    exit;
}

$id_pemesanan = (int)$_POST['id_pemesanan'];
$aksi = $_POST['aksi'];
$id_admin = $_SESSION['id_admin'];

// Cek aksi valid
if (!in_array($aksi, ['konfirmasi', 'tolak'])) {
    $_SESSION['error_message'] = "Aksi tidak dikenal.";
    header('Location: data_pesanan.php');
    exit;
}

// Ambil data pemesanan
$sql = "
    SELECT p.nama_pemesan, p.email, v.nama AS nama_vila
    FROM pemesanan p
    JOIN vila v ON p.id_vila = v.id
    WHERE p.id = ? AND v.id_admin = ?
";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("ii", $id_pemesanan, $id_admin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Pemesanan tidak ditemukan atau tidak memiliki akses.";
    header('Location: data_pesanan.php');
    exit;
}

$data = $result->fetch_assoc();
$status_baru = ($aksi === 'konfirmasi') ? 'dikonfirmasi' : 'ditolak';
$aksi_text = ($aksi === 'konfirmasi') ? 'dikonfirmasi' : 'ditolak';

// Update status pemesanan
$update = $koneksi->prepare("UPDATE pemesanan SET status_konfirmasi = ? WHERE id = ?");
$update->bind_param("si", $status_baru, $id_pemesanan);

if ($update->execute()) {
    $_SESSION['success_message'] = "Pemesanan atas nama {$data['nama_pemesan']} berhasil {$aksi_text}.";

    // Kirim email
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi server SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ahmadsopiandi989@gmail.com'; // Ganti
        $mail->Password = 'ncyu pppk dszs rycl';         // Ganti app password Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('ahmadsopiandi989@gmail.com', 'JelajahVilla');
        $mail->addAddress($data['email'], $data['nama_pemesan']);
        $mail->isHTML(true);

        if ($aksi === 'konfirmasi') {
            $mail->Subject = 'Pemesanan Dikonfirmasi - JelajahVilla';
            $mail->Body = "
                <p>Halo <strong>{$data['nama_pemesan']}</strong>,</p>
                <p>Pemesanan Anda untuk vila <strong>{$data['nama_vila']}</strong> telah <span style='color:green;'>dikonfirmasi</span>.</p>
                <p>Silakan lanjutkan proses selanjutnya sesuai petunjuk pembayaran.</p>
                <p>Terima kasih telah menggunakan JelajahVilla.</p>
            ";
        } else {
            $mail->Subject = 'Pemesanan Ditolak - JelajahVilla';
            $mail->Body = "
                <p>Halo <strong>{$data['nama_pemesan']}</strong>,</p>
                <p>Mohon maaf, pemesanan Anda untuk vila <strong>{$data['nama_vila']}</strong> telah <span style='color:red;'>ditolak</span>.</p>
                <p>Anda dapat mencoba memesan vila lain atau menghubungi admin untuk informasi lebih lanjut.</p>
                <p>Terima kasih telah menggunakan JelajahVilla.</p>
            ";
        }

        $mail->send();
    } catch (Exception $e) {
        // Email gagal dikirim, tapi tidak blok proses utama
        error_log("Email gagal: " . $mail->ErrorInfo);
    }
} else {
    $_SESSION['error_message'] = "Gagal memperbarui status pemesanan.";
}

header('Location: data_pesanan.php');
exit;
