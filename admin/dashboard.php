<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

include '../DB/koneksi.php';
$id_admin = $_SESSION['id_admin'];
$query = mysqli_query($koneksi, "SELECT * FROM vila WHERE id_admin = $id_admin");

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin - JelajahVilla.com</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
 
</head>
<body>
      <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container d-flex justify-content-between align-items-center w-100">
        <a class="navbar-brand" href="#">JelajahVilla.com - Dashboard Admin</a>
        <div class="ml-auto d-flex">
          <span class="text-white mr-3">Hallo, <?= htmlspecialchars($_SESSION['username']); ?>!</span>
          <a href="../vilago/index.php" class="btn btn-light mr-2">Cari Vila</a>
          <a href="../vilago/logout.php" class="btn btn-danger">Logout</a>
        </div>
      </div>
    </nav>


  <div class="container my-5">
    <h2 class="text-center mb-4">Daftar Vila</h2>
    <div class="d-flex justify-content-between mb-3">
      <a href="tambah.php" class="btn btn-primary">+ Tambah Vila</a>
      <a href="data_pesanan.php" class="btn btn-primary">Lihat Data Pesanan</a>
    </div>

    <div class="row">
      <?php while ($vila = mysqli_fetch_assoc($query)) : ?>
        <?php
          $idVila = $vila['id'];
          // Ambil foto cover jika ada, jika tidak ambil 1 foto, jika tetap tidak ada tampilkan default
          $fotoQuery = mysqli_query($koneksi, "SELECT nama_file FROM foto_vila WHERE id_vila = $idVila AND is_cover = 1 LIMIT 1");
          $foto = mysqli_fetch_assoc($fotoQuery);
          if (!$foto) {
              $fotoQuery = mysqli_query($koneksi, "SELECT nama_file FROM foto_vila WHERE id_vila = $idVila LIMIT 1");
              $foto = mysqli_fetch_assoc($fotoQuery);
          }
          $gambar = $foto ? $foto['nama_file'] : 'default.jpg';
        ?>
        <div class="col-md-4">
          <div class="card mb-4 shadow-sm">
            <img src="../img/<?= htmlspecialchars($gambar) ?>" class="card-img-top" alt="<?= htmlspecialchars($vila['nama']) ?>">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($vila['nama']) ?></h5>
              <p class="card-text">Lokasi: <?= htmlspecialchars($vila['lokasi']) ?></p>
              <p class="card-text"><strong>Rp <?= number_format($vila['harga'], 0, ',', '.') ?> / malam</strong></p>
              <div class="d-flex justify-content-between">
                <a href="edit.php?id=<?= $vila['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="hapus.php?type=vila&id=<?= $vila['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus vila ini?')">Hapus</a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-4">&copy; 2025 JelajahVilla</footer>

</body>
</html>