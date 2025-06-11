<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['id_admin'])) {
    header("Location: ../login.php");
    exit;
}

include '../DB/koneksi.php';

// Ambil id admin dari session
$id_admin = $_SESSION['id_admin'];

if (isset($_POST['submit'])) {
    // Ambil data POST
    $nama = trim($_POST['nama']);
    $lokasi = trim($_POST['lokasi']);
    $harga = trim($_POST['harga']);
    $deskripsi = trim($_POST['deskripsi']);
    $kontak = trim($_POST['kontak']);
    $email = trim($_POST['email']);
    $kategori = trim($_POST['kategori']);
    $maps_embed = trim($_POST['maps_embed']);

    $nama_pemilik = trim($_POST['nama_pemilik']);
$bank = trim($_POST['bank']);
$no_rekening = trim($_POST['no_rekening']);


    // Validasi wajib isi semua
    if ($nama === "" || $lokasi === "" || $harga === "" || $deskripsi === "" || 
        $kontak === "" || $email === "" || $kategori === "" || empty($_FILES['gambar']['name'][0])) {
        $error = "Semua field harus diisi dan minimal 1 foto wajib diupload.";
    } else {
        // Validasi fasilitas: harus semua fasilitas terisi, dan fasilitas minimal 1
        if (empty($_POST['fasilitas_nama'])) {
            $error = "Minimal 1 fasilitas harus ditambahkan.";
        } else {
            $fasilitas_valid = true;
            foreach ($_POST['fasilitas_nama'] as $fasilitas) {
                if (trim($fasilitas) === "") {
                    $fasilitas_valid = false;
                    break;
                }
            }
            if (!$fasilitas_valid) {
                $error = "Semua fasilitas harus diisi.";
            }
        }
    }

    if (!isset($error)) {
        // Escape input sebelum insert
        $nama = mysqli_real_escape_string($koneksi, $nama);
        $lokasi = mysqli_real_escape_string($koneksi, $lokasi);
        $harga = mysqli_real_escape_string($koneksi, $harga);
        $deskripsi = mysqli_real_escape_string($koneksi, $deskripsi);
        $kontak = mysqli_real_escape_string($koneksi, $kontak);
        $email = mysqli_real_escape_string($koneksi, $email);
        $kategori = mysqli_real_escape_string($koneksi, $kategori);
        $maps_embed = mysqli_real_escape_string($koneksi, $maps_embed);
        $nama_pemilik = mysqli_real_escape_string($koneksi, $nama_pemilik);
        $bank = mysqli_real_escape_string($koneksi, $bank);
        $no_rekening = mysqli_real_escape_string($koneksi, $no_rekening);
        
        // Insert data ke database seperti biasa...
        $sql = "INSERT INTO vila (nama, lokasi, harga, deskripsi, kontak, email, kategori, maps_embed, id_admin, nama_pemilik, bank, no_rekening) 
        VALUES ('$nama', '$lokasi', '$harga', '$deskripsi', '$kontak', '$email', '$kategori', '$maps_embed', '$id_admin', '$nama_pemilik', '$bank', '$no_rekening')";


        if (mysqli_query($koneksi, $sql)) {
            $id_vila = mysqli_insert_id($koneksi);
            $folder = "../img/";
            $jumlah = count($_FILES['gambar']['name']);

            for ($i = 0; $i < $jumlah; $i++) {
                $nama_file = $_FILES['gambar']['name'][$i];
                $tmp_name = $_FILES['gambar']['tmp_name'][$i];
                $upload_path = $folder . basename($nama_file);

                if (move_uploaded_file($tmp_name, $upload_path)) {
                    $is_cover = ($i === 0) ? 1 : 0;
                    mysqli_query($koneksi, "INSERT INTO foto_vila (id_vila, nama_file, is_cover) 
                                            VALUES ('$id_vila', '$nama_file', '$is_cover')");
                }
            }

            // Simpan fasilitas
            foreach ($_POST['fasilitas_nama'] as $nama_fasilitas) {
                $nama_fasilitas = mysqli_real_escape_string($koneksi, $nama_fasilitas);
                mysqli_query($koneksi, "INSERT INTO fasilitas_vila (id_vila, nama_fasilitas)
                                        VALUES ('$id_vila', '$nama_fasilitas')");
            }

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Gagal menambahkan vila: " . mysqli_error($koneksi);
        }
    }
}

?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Tambah Vila - Admin JelajahVilla.com</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5" style="max-width: 700px;">
    <h2 class="mb-4">Tambah Vila Baru</h2>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label>Nama Vila</label>
        <input type="text" name="nama" class="form-control" required />
    </div>
    <div class="form-group">
        <label>Lokasi</label>
        <input type="text" name="lokasi" class="form-control" required />
    </div>
    <div class="form-group">
        <label>Harga per malam (Rp)</label>
        <input type="number" name="harga" class="form-control" required min="0" />
    </div>
    <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control" rows="4" required></textarea>
    </div>

    <hr>
    <h4>Fasilitas</h4>
    <div id="fasilitas-wrapper">
        <div class="fasilitas-item mb-3">
            <input type="text" name="fasilitas_nama[]" class="form-control" placeholder="Nama fasilitas" required />
        </div>
    </div>
    <button type="button" id="tambah-fasilitas" class="btn btn-info mb-3">Tambah Fasilitas Lagi</button>

    <!-- Pindahkan input Nama Pemilik, Bank, dan No Rekening di sini -->
    <div class="form-group">
        <label>Nama Pemilik</label>
        <input type="text" name="nama_pemilik" class="form-control" required />
    </div>
    <div class="form-group">
        <label>Bank</label>
        <input type="text" name="bank" class="form-control" required />
    </div>
    <div class="form-group">
        <label>Nomor Rekening</label>
        <input type="text" name="no_rekening" class="form-control" required />
    </div>

    <div class="form-group">
        <label>Kontak</label>
        <input type="text" name="kontak" class="form-control" required />
    </div>
    <div class="form-group">
        <label>Email Pemilik</label>
        <input type="email" name="email" class="form-control" required />
    </div>
    <div class="form-group">
        <label>Kategori</label>
        <select name="kategori" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <option value="pantai">Pantai</option>
            <option value="pengunungan">Pegunungan</option>
            <option value="tengah kota">Tengah Kota</option>
        </select>
    </div>
    <div class="form-group">
        <label>Embed Google Maps</label>
        <textarea name="maps_embed" class="form-control" rows="3" placeholder='Contoh: &lt;iframe src="https://www.google.com/maps/embed?..."&gt;&lt;/iframe&gt;'></textarea>
    </div>
    <div class="form-group">
        <label>Foto Vila (boleh lebih dari satu)</label>
        <label>Foto pertama yang dipilih akan menjadi tampilan utama, sisanya sebagai foto fasilitas.</label>
        <input type="file" name="gambar[]" class="form-control-file" accept="image/*" multiple required />
    </div>

    <button type="submit" name="submit" class="btn btn-success">Tambah Vila</button>
    <a href="dashboard.php" class="btn btn-secondary">Batal</a>
</form>

</div>

<script>
    document.getElementById('tambah-fasilitas').addEventListener('click', function () {
        const wrapper = document.getElementById('fasilitas-wrapper');
        const newItem = document.createElement('div');
        newItem.classList.add('fasilitas-item', 'mb-3');
        newItem.innerHTML = `
            <input type="text" name="fasilitas_nama[]" class="form-control" placeholder="Nama fasilitas" required />
        `;
        wrapper.appendChild(newItem);
    });
</script>

</body>
</html>
