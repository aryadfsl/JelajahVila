<?php
include '../DB/koneksi.php';
session_start();
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit;
}

// Validasi input
if (!isset($_POST['id_pemesanan'], $_POST['aksi']) || !is_numeric($_POST['id_pemesanan'])) {
    $_SESSION['error_message'] = "Permintaan tidak valid.";
    header('Location: data_pesanan.php');
    exit;
}

$id_pemesanan = (int)$_POST['id_pemesanan'];
$aksi = $_POST['aksi'];
$id_admin = $_SESSION['id_admin'];

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

    // Kirim email ke pemesan
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ahmadsopiandi989@gmail.com'; // Ganti dengan email kamu
        $mail->Password = 'ncyu pppk dszs rycl';         // Ganti dengan App Password Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('ahmadsopiandi989@gmail.com', 'JelajahVilla');
        $mail->addAddress($data['email'], $data['nama_pemesan']);
        $mail->isHTML(true);

        if ($aksi === 'konfirmasi') {
            $mail->Subject = 'Pemesanan Dikonfirmasi - JelajahVilla';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background:#f9f9f9; padding: 20px; border-radius: 8px; border:1px solid #ddd;'>
                    <h2 style='color: #26C6DA; text-align: center;'>JelajahVilla</h2>
                    <p>Halo <strong>{$data['nama_pemesan']}</strong>,</p>
                    <p>Pemesanan Anda untuk vila <strong>{$data['nama_vila']}</strong> telah 
                        <span style='color: green; font-weight: bold;'>dikonfirmasi</span>.</p>
                    <div style='background: #fff; padding: 15px; border-radius: 6px; border: 1px solid #ccc; margin: 20px 0;'>
                        <p><strong>Langkah selanjutnya:</strong></p>
                        <ol style='padding-left: 20px;'>
                            <li>Periksa instruksi pembayaran pada halaman riwayat pemesanan Anda.</li>
                            <li>Lakukan pembayaran sesuai metode dan nominal yang tertera.</li>
                            <li>Unggah bukti pembayaran agar proses verifikasi dapat dilanjutkan.</li>
                        </ol>
                    </div>
                    <p>Jika ada pertanyaan, jangan ragu untuk menghubungi kami.</p>
                    <p>Terima kasih telah menggunakan <strong>JelajahVilla</strong>.</p>
                    <hr style='margin-top: 30px; border-color: #eee;'>
                    <p style='font-size: 12px; color: #888; text-align: center;'>Email ini dikirim otomatis oleh sistem JelajahVilla. Mohon tidak membalas email ini.</p>
                </div>
            ";
        } else {
            $mail->Subject = 'Pemesanan Ditolak - JelajahVilla';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background:#f9f9f9; padding: 20px; border-radius: 8px; border:1px solid #ddd;'>
                    <h2 style='color: #FF6E40; text-align: center;'>JelajahVilla</h2>
                    <p>Halo <strong>{$data['nama_pemesan']}</strong>,</p>
                    <p>Mohon maaf, pemesanan Anda untuk vila <strong>{$data['nama_vila']}</strong> telah 
                        <span style='color: red; font-weight: bold;'>ditolak</span>.</p>
                    <p>Anda dapat mencoba memesan vila lain atau menghubungi admin untuk informasi lebih lanjut.</p>
                    <p>Terima kasih telah menggunakan <strong>JelajahVilla</strong>.</p>
                    <hr style='margin-top: 30px; border-color: #eee;'>
                    <p style='font-size: 12px; color: #888; text-align: center;'>Email ini dikirim otomatis oleh sistem JelajahVilla. Mohon tidak membalas email ini.</p>
                </div>
            ";
        }

        $mail->send(); // <-- INI YANG PENTING

    } catch (Exception $e) {
        error_log("Gagal mengirim email: {$mail->ErrorInfo}");
    }

} else {
    $_SESSION['error_message'] = "Gagal memperbarui status pemesanan.";
}

header('Location: data_pesanan.php');
exit;
