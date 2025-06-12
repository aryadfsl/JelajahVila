<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['id_admin'])) {
    header("Location: ../login.php");
    exit;
}

include '../DB/koneksi.php';

$id_admin = $_SESSION['id_admin'];

if (!isset($_GET['id'])) {
    echo "<script>alert('ID vila tidak ditemukan.'); window.location='dashboard.php';</script>";
    exit;
}

$id = intval($_GET['id']);

// Ambil data vila dan pastikan vila milik admin yang sedang login
$vilaQuery = mysqli_query($koneksi, "SELECT * FROM vila WHERE id = $id AND id_admin = $id_admin");
if (mysqli_num_rows($vilaQuery) == 0) {
    echo "<script>alert('Vila tidak ditemukan atau bukan milik Anda.'); window.location='dashboard.php';</script>";
    exit;
}
$vila = mysqli_fetch_assoc($vilaQuery);

// Ambil fasilitas vila
$fasilitasQuery = mysqli_query($koneksi, "SELECT nama_fasilitas FROM fasilitas_vila WHERE id_vila = $id");
$fasilitas_arr = [];
while ($f = mysqli_fetch_assoc($fasilitasQuery)) {
    $fasilitas_arr[] = htmlspecialchars($f['nama_fasilitas']);
}

// Proses form submit
if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $harga = intval($_POST['harga']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $maps_embed = mysqli_real_escape_string($koneksi, $_POST['maps_embed']);
    $kontak = mysqli_real_escape_string($koneksi, $_POST['kontak']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);

    // Update data vila
    $update = mysqli_query($koneksi, "UPDATE vila SET 
        nama='$nama', 
        lokasi='$lokasi', 
        harga=$harga, 
        deskripsi='$deskripsi', 
        kategori='$kategori',
        maps_embed='$maps_embed',
        kontak='$kontak',
        email='$email'
        WHERE id=$id AND id_admin=$id_admin");

    if ($update) {
        // Hapus fasilitas lama
        mysqli_query($koneksi, "DELETE FROM fasilitas_vila WHERE id_vila = $id");

        // Tambah fasilitas baru
        if (!empty($_POST['fasilitas'])) {
            foreach ($_POST['fasilitas'] as $fas) {
                $fas = trim(mysqli_real_escape_string($koneksi, $fas));
                if ($fas !== '') {
                    mysqli_query($koneksi, "INSERT INTO fasilitas_vila (id_vila, nama_fasilitas) VALUES ($id, '$fas')");
                }
            }
        }

        // Upload foto baru jika ada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['foto']['name'];
            $filetmp = $_FILES['foto']['tmp_name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $newFileName = uniqid() . '.' . $ext;
                $destination = "../img/" . $newFileName;

                if (move_uploaded_file($filetmp, $destination)) {
                    mysqli_query($koneksi, "INSERT INTO foto_vila (id_vila, nama_file) VALUES ($id, '$newFileName')");
                }
            }
        }

        echo "<script>alert('Data vila berhasil diperbarui.'); window.location='dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal memperbarui data vila.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Vila - Dashboard Admin</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container my-5">
  <h2>Edit Data Vila</h2>
  <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label>Nama Vila</label>
      <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($vila['nama']) ?>" required />
    </div>
    <div class="form-group">
      <label>Lokasi</label>
      <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($vila['lokasi']) ?>" required />
    </div>
    <div class="form-group">
      <label>Harga per malam (Rp)</label>
      <input type="number" name="harga" class="form-control" value="<?= htmlspecialchars($vila['harga']) ?>" required />
    </div>
    <div class="form-group">
      <label>Deskripsi</label>
      <textarea name="deskripsi" class="form-control" rows="4"><?= htmlspecialchars($vila['deskripsi']) ?></textarea>
    </div>

    <div class="form-group">
      <label>Kontak</label>
      <input type="text" name="kontak" class="form-control" value="<?= htmlspecialchars($vila['kontak']) ?>" required />
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($vila['email']) ?>" required />
    </div>

    <div class="form-group">
      <label>Kategori</label>
      <select name="kategori" class="form-control" required>
        <option value="Rekomendasi" <?= $vila['kategori'] == 'Rekomendasi' ? 'selected' : '' ?>>Rekomendasi</option>
        <option value="Promo" <?= $vila['kategori'] == 'Promo' ? 'selected' : '' ?>>Promo</option>
        <option value="Pantai" <?= $vila['kategori'] == 'Pantai' ? 'selected' : '' ?>>Pantai</option>
        <option value="Pegunungan" <?= $vila['kategori'] == 'Pegunungan' ? 'selected' : '' ?>>Pegunungan</option>
        <option value="Tengah Kota" <?= $vila['kategori'] == 'Tengah Kota' ? 'selected' : '' ?>>Tengah Kota</option>
      </select>
    </div>

    <div class="form-group">
      <label>Embed Google Maps</label>
      <textarea name="maps_embed" class="form-control" rows="3" placeholder="Masukkan iframe Google Maps"><?= htmlspecialchars($vila['maps_embed']) ?></textarea>
      <small class="form-text text-muted">Masukkan kode iframe dari Google Maps (bukan hanya link).</small>
    </div>

    <div class="form-group">
      <label>Fasilitas</label>
      <div id="fasilitas-wrapper">
        <?php foreach ($fasilitas_arr as $fas): ?>
          <div class="input-group mb-2 fasilitas-item">
            <input type="text" name="fasilitas[]" class="form-control" value="<?= $fas ?>" required />
            <div class="input-group-append">
              <button class="btn btn-danger btn-remove-fasilitas" type="button">&times;</button>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($fasilitas_arr)) : ?>
          <div class="input-group mb-2 fasilitas-item">
            <input type="text" name="fasilitas[]" class="form-control" placeholder="Tambah fasilitas" />
            <div class="input-group-append">
              <button class="btn btn-danger btn-remove-fasilitas" type="button">&times;</button>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" id="btn-add-fasilitas" class="btn btn-secondary btn-sm">Tambah Fasilitas</button>
    </div>

    <div class="form-group">
      <label>Tambah Foto Baru</label>
      <input type="file" name="foto" class="form-control-file" accept=".jpg,.jpeg,.png,.gif" />
    </div>

    <div class="form-group">
      <label>Foto Vila Saat Ini</label><br />
      <div class="row">
        <?php
        $fotoQuery = mysqli_query($koneksi, "SELECT * FROM foto_vila WHERE id_vila = $id");
        while ($foto = mysqli_fetch_assoc($fotoQuery)) {
            $foto_id = $foto['id'];
            $foto_file = htmlspecialchars($foto['nama_file']);
            echo '<div class="col-md-3 mb-3 text-center">';
            echo '<img src="../img/' . $foto_file . '" class="img-thumbnail" style="width:100%; max-height:150px; object-fit:cover;" alt="Foto Vila">';
            echo '<br>';
            echo '<a href="#" class="btn btn-sm btn-danger mt-2 btn-delete-foto" data-id="' . $foto_id . '" data-vila="' . $id . '">Hapus</a>';
            echo '</div>';
        }
        ?>
      </div>

      <!-- Tombol hapus semua foto -->
      <button id="btn-delete-all" class="btn btn-danger mb-3">Hapus Semua Foto</button>
    </div>

    <button type="submit" name="submit" class="btn btn-primary">Simpan Perubahan</button>
    <a href="dashboard.php" class="btn btn-secondary">Batal</a>
  </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function() {
  // Tambah fasilitas
  $('#btn-add-fasilitas').click(function() {
    const html = `
      <div class="input-group mb-2 fasilitas-item">
        <input type="text" name="fasilitas[]" class="form-control" placeholder="Tambah fasilitas" />
        <div class="input-group-append">
          <button class="btn btn-danger btn-remove-fasilitas" type="button">&times;</button>
        </div>
      </div>`;
    $('#fasilitas-wrapper').append(html);
  });

  // Hapus fasilitas input
  $(document).on('click', '.btn-remove-fasilitas', function() {
    $(this).closest('.fasilitas-item').remove();
  });

  // Modal untuk hapus foto tunggal
  $('body').append(`
  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmDeleteLabel">Konfirmasi Hapus Foto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">Apakah Anda yakin ingin menghapus foto ini?</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <a href="#" class="btn btn-danger" id="btn-confirm-delete">Ya, Hapus</a>
        </div>
      </div>
    </div>
  </div>
  `);

  // Modal untuk hapus semua foto
  $('body').append(`
  <div class="modal fade" id="confirmDeleteAllModal" tabindex="-1" aria-labelledby="confirmDeleteAllLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmDeleteAllLabel">Konfirmasi Hapus Semua Foto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">Apakah Anda yakin ingin menghapus semua foto vila ini?</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <a href="hapus.php?type=all_foto&id=<?= $id ?>" class="btn btn-danger" id="btn-confirm-delete-all">Ya, Hapus Semua</a>
        </div>
      </div>
    </div>
  </div>
  `);

  $('.btn-delete-foto').click(function(e) {
  e.preventDefault();
  const fotoId = $(this).data('id');
  const vilaId = $(this).data('vila'); // sudah ambil vila_id
  $('#btn-confirm-delete').attr('href', 'hapus.php?type=foto&id=' + fotoId + '&vila_id=' + vilaId);
  $('#confirmDeleteModal').modal('show');
});


  // Tombol hapus semua foto
  $('#btn-delete-all').click(function(e) {
    e.preventDefault();
    $('#confirmDeleteAllModal').modal('show');
  });
});
</script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
