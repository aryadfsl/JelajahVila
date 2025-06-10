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

        // Email ke pemilik vila (tanpa nomor VA)
        $mail->clearAddresses();
        $mail->addAddress($email_pemilik, 'Pemilik Vila');
        $mail->isHTML(true);
        $mail->Subject = "Pemesanan Baru untuk Vila $nama_vila";
        $mail->Body = "
            <h3>Anda mendapatkan pemesanan baru!</h3>
            <p><b>Nama Pemesan:</b> $nama</p>
            <p><b>Email Pemesan:</b> $email</p>
            <p><b>Telepon Pemesan:</b> $telepon</p>
            <p><b>Check-in:</b> $checkin</p>
            <p><b>Check-out:</b> $checkout</p>
            <p><b>Jumlah Orang:</b> $tamu</p>
            <p><b>Harga Total:</b> Rp " . number_format($harga, 0, ',', '.') . "</p>
            <p><b>Metode Pembayaran:</b> $metode</p>
        ";
        $mail->send();

       

        // Email ke pemesan
        $mail->clearAddresses();
        $mail->addAddress($email, $nama);
        $mail->Subject = "Konfirmasi Pemesanan Anda di Vila $nama_vila";
        $mail->Body = "
            <h3>Terima kasih sudah memesan vila kami!</h3>
            <p>Berikut detail pemesanan Anda:</p>
            <p><b>Nama Vila:</b> $nama_vila</p>
            <p><b>Check-in:</b> $checkin</p>
            <p><b>Check-out:</b> $checkout</p>
            <p><b>Jumlah Orang:</b> $tamu</p>
            <p><b>Harga Total:</b> Rp " . number_format($harga, 0, ',', '.') . "</p>
            <p>Mohon tunggu konfirmasi dari pemilik vila terima kasih</p>
            $info_pembayaran
        ";
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
