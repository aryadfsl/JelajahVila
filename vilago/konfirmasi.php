<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
include '../DB/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_vila = intval($_POST['id_vila']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $telepon = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $tamu = intval($_POST['tamu']);
    $harga = floatval($_POST['harga']);
    $metode = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($telepon) || empty($metode)) {
        die("Form belum lengkap. Harap isi semua data.");
    }

    // Insert data pemesanan ke database
    $query = "INSERT INTO pemesanan 
              (id_vila, nama_pemesan, email, telepon, tanggal_checkin, tanggal_checkout, jumlah_orang, harga, metode_pembayaran)
              VALUES 
              ('$id_vila', '$nama', '$email', '$telepon', '$checkin', '$checkout', '$tamu', '$harga', '$metode')";
    $result = mysqli_query($koneksi, $query);

    if (!$result) {
        die("Gagal menyimpan data pemesanan: " . mysqli_error($koneksi));
    }

    // Dapatkan ID pemesanan yang baru saja disimpan
    $id_pemesanan = mysqli_insert_id($koneksi);

    // Upload bukti transfer jika metode Transfer Bank dan file ada
    if ($metode === 'Transfer Bank' && isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../img/bukti_transfer/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath = $_FILES['bukti_transfer']['tmp_name'];
        $fileName = basename($_FILES['bukti_transfer']['name']);
        $fileSize = $_FILES['bukti_transfer']['size'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validasi tipe file
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if (!in_array($fileType, $allowedTypes)) {
            die("Format file bukti transfer tidak didukung. Hanya JPG, PNG, GIF, dan PDF yang diperbolehkan.");
        }

        // Buat nama file unik supaya tidak overwrite
        $newFileName = 'bukti_' . $id_pemesanan . '_' . time() . '.' . $fileType;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Update database dengan nama file bukti transfer
            $updateQuery = "UPDATE pemesanan SET bukti_transfer = '$newFileName' WHERE id = $id_pemesanan";
            mysqli_query($koneksi, $updateQuery);
        } else {
            die("Gagal mengupload bukti transfer.");
        }
    }

    // Ambil data vila
    $vila_q = mysqli_query($koneksi, "SELECT * FROM vila WHERE id = $id_vila");
    $vila = mysqli_fetch_assoc($vila_q);
    $email_pemilik = $vila['email'];
    $nama_vila = $vila['nama'];

    // Kirim email
    $mail = new PHPMailer(true);
    try {
        // Setup SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ahmadsopiandi989@gmail.com';    // Ganti dengan email Gmail pengirim
        $mail->Password = 'ncyu pppk dszs rycl';           // Ganti dengan App Password Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('emailkamu@gmail.com', 'JelajahVilla');

        $mail->clearAddresses();
$mail->addAddress($email_pemilik, 'Pemilik Vila');
$mail->Subject = "Pemesanan Baru untuk Vila $nama_vila";
$mail->isHTML(true);
$mail->Body = '
  <div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 8px;">
      <h2 style="color: #FF7043;">Ada pemesanan baru untuk vila Anda!</h2>
      <p style="font-size: 15px; color: #333;">Detail pemesanan:</p>

      <table style="width: 100%; table-layout: fixed; font-size: 14px; border-collapse: collapse;">
        <tr>
          <td style="padding: 8px 4px; width: 35%;"><strong>Nama Pemesan:</strong></td>
          <td style="padding: 8px 4px; width: 65%;">' . htmlspecialchars($nama) . '</td>
        </tr>
        <tr>
          <td style="padding: 8px 4px;"><strong>Email:</strong></td>
          <td style="padding: 8px 4px; overflow-wrap: break-word; word-break: break-word;">' . htmlspecialchars($email) . '</td>
        </tr>
        <tr>
          <td style="padding: 8px 4px;"><strong>Telepon:</strong></td>
          <td style="padding: 8px 4px;">' . htmlspecialchars($telepon) . '</td>
        </tr>
        <tr>
          <td style="padding: 8px 4px;"><strong>Check-in:</strong></td>
          <td style="padding: 8px 4px;">' . $checkin . '</td>
        </tr>
        <tr>
          <td style="padding: 8px 4px;"><strong>Check-out:</strong></td>
          <td style="padding: 8px 4px;">' . $checkout . '</td>
        </tr>
        <tr>
          <td style="padding: 8px 4px;"><strong>Jumlah Orang:</strong></td>
          <td style="padding: 8px 4px;">' . $tamu . '</td>
        </tr>
        <tr>
          <td style="padding: 8px 4px;"><strong>Pembayaran:</strong></td>
          <td style="padding: 8px 4px;">' . htmlspecialchars($metode) . '</td>
        </tr>
        <tr>
          <td style="padding: 8px 4px;"><strong>Total Harga:</strong></td>
          <td style="padding: 8px 4px;"><strong>Rp ' . number_format($harga, 0, ',', '.') . '</strong></td>
        </tr>
      </table>

      <p style="margin-top: 20px; font-size: 14px; color: #333;">
        Silakan login ke dashboard untuk konfirmasi atau tindak lanjut.
      </p>

      <p style="font-size: 12px; color: #999; margin-top: 30px;">
        Email ini dikirim otomatis oleh sistem JelajahVilla.
      </p>
    </div>
  </div>';
$mail->send();

        

        $mail->clearAddresses();
        $mail->addAddress($email, $nama);
        $mail->Subject = "Konfirmasi Pemesanan Anda di Vila $nama_vila";
        $mail->isHTML(true);
        $mail->Body = '
          <div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px;">
            <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 8px;">
              <h2 style="color: #00BFA6;">Terima kasih telah memesan di JelajahVilla!</h2>
              <p style="font-size: 15px; color: #333;">Halo <strong>' . htmlspecialchars($nama) . '</strong>, berikut ini adalah detail pemesanan Anda:</p>
        
              <table style="width: 100%; font-size: 14px; margin-top: 20px; border-collapse: collapse;">
                <tr><td style="padding: 8px 4px;"><strong>Nama Vila:</strong></td><td style="padding: 8px 4px;">' . htmlspecialchars($nama_vila) . '</td></tr>
                <tr><td style="padding: 8px 4px;"><strong>Check-in:</strong></td><td style="padding: 8px 4px;">' . $checkin . '</td></tr>
                <tr><td style="padding: 8px 4px;"><strong>Check-out:</strong></td><td style="padding: 8px 4px;">' . $checkout . '</td></tr>
                <tr><td style="padding: 8px 4px;"><strong>Jumlah Orang:</strong></td><td style="padding: 8px 4px;">' . $tamu . '</td></tr>
                <tr><td style="padding: 8px 4px;"><strong>Metode Pembayaran:</strong></td><td style="padding: 8px 4px;">' . htmlspecialchars($metode) . '</td></tr>
                <tr><td style="padding: 8px 4px;"><strong>Email:</strong></td><td style="padding: 8px 4px; word-wrap: break-word; overflow-wrap: break-word;">' . htmlspecialchars($email) . '</td></tr>
                <tr><td style="padding: 8px 4px;"><strong>Telepon:</strong></td><td style="padding: 8px 4px;">' . htmlspecialchars($telepon) . '</td></tr>
                <tr><td style="padding: 8px 4px;"><strong>Total Harga:</strong></td><td style="padding: 8px 4px;"><strong>Rp ' . number_format($harga, 0, ',', '.') . '</strong></td></tr>
              </table>
        
              <p style="margin-top: 20px; font-size: 14px; color: #333;">
                Mohon tunggu konfirmasi dari pemilik vila. Anda akan menerima informasi lebih lanjut setelah pemesanan dikonfirmasi.
              </p>
        
        
              <p style="font-size: 12px; color: #999; margin-top: 30px;">
                Email ini dikirim secara otomatis oleh sistem JelajahVilla.
              </p>
            </div>
          </div>';
        $mail->send();
        

        // Redirect ke halaman detail dengan pesan sukses
        header("Location: ../user/riwayat_pemesan.php");
        exit;

    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        header("Location: ../user/riwayat_pemesan.php");
        exit;
    }
} else {
    header("Location: ../user/riwayat_pemesan.php");
    exit;
}
