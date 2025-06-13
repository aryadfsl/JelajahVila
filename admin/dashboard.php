<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

include '../DB/koneksi.php';

$id_admin = $_SESSION['id_admin'];

// Ambil semua vila milik admin
$query = mysqli_query($koneksi, "SELECT * FROM vila WHERE id_admin = $id_admin");

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Admin - JelajahVilla.com</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/dashboard.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container d-flex justify-content-between align-items-center w-100">
    <a class="navbar-brand" href="#">
      <i class="fas fa-home-alt mr-2"></i>JelajahVilla.com
    </a>
    <div class="ml-auto d-flex align-items-center">
      <span class="text-white mr-3">
        <i class="fas fa-user-circle mr-1"></i>
        Hallo, <?= htmlspecialchars($_SESSION['username']); ?>!
      </span>
      <a href="../vilago/index.php" class="btn btn-search mr-2">
        <i class="fas fa-search mr-1"></i>Cari Vila
      </a>
      <a href="../auth/logout.php" class="btn btn-logout">
        <i class="fas fa-sign-out-alt mr-1"></i>Logout
      </a>
    </div>
  </div>
</nav>

<div class="container my-5">
  <h1 class="page-title">
    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard Vila
  </h1>
  
  <div class="action-buttons d-flex justify-content-between align-items-center mb-4">
    <a href="tambah.php" class="btn-gradient-primary">
      <i class="fas fa-plus mr-2"></i>Tambah Vila Baru
    </a>
    <a href="data_pesanan.php" class="btn-gradient-secondary">
      <i class="fas fa-chart-line mr-2"></i>Lihat Data Pesanan
    </a>
  </div>

  <div class="row">
    <?php if (mysqli_num_rows($query) > 0): ?>
      <?php while ($vila = mysqli_fetch_assoc($query)) : ?>
        <?php
          $idVila = $vila['id'];

          // Ambil gambar cover atau fallback
          $fotoQuery = mysqli_query($koneksi, "SELECT nama_file FROM foto_vila WHERE id_vila = $idVila AND is_cover = 1 LIMIT 1");
          $foto = mysqli_fetch_assoc($fotoQuery);
          if (!$foto) {
              $fotoQuery = mysqli_query($koneksi, "SELECT nama_file FROM foto_vila WHERE id_vila = $idVila LIMIT 1");
              $foto = mysqli_fetch_assoc($fotoQuery);
          }
          $gambar = $foto ? $foto['nama_file'] : 'default.jpg';

          // Hitung jumlah penyewa
          $penyewaQuery = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM pemesanan WHERE id_vila = $idVila");
          $jumlahPenyewa = mysqli_fetch_assoc($penyewaQuery)['jumlah'];
        ?>
        <div class="col-md-4 mb-4">
          <div class="card villa-card h-100 position-relative">
            <?php if ($jumlahPenyewa > 1): ?>
              <div class="favorite-icon" title="Vila Terpopuler" style="position:absolute; top:10px; right:10px; font-size:24px; color: gold;">
                <i class="fas fa-crown"></i>
              </div>
            <?php endif; ?>
            
            <div style="position: relative; overflow: hidden;">
              <img src="../img/<?= htmlspecialchars($gambar) ?>" class="img-fluid" alt="<?= htmlspecialchars($vila['nama']) ?>" />
            </div>
            
            <div class="card-body d-flex flex-column">
              <h5 class="villa-title"><?= htmlspecialchars($vila['nama']) ?></h5>
              <p class="villa-location">
                <i class="fas fa-map-marker-alt mr-1"></i>
                <?= htmlspecialchars($vila['lokasi']) ?>
              </p>
              <p class="villa-price">
                <i class="fas fa-money-bill-wave mr-1"></i>
                Rp <?= number_format($vila['harga'], 0, ',', '.') ?> / malam
              </p>
              <div class="villa-stats">
                <i class="fas fa-users mr-1"></i>
                <?= $jumlahPenyewa ?> penyewa
              </div>
              
              <div class="mt-auto">
                <div class="d-flex justify-content-between">
                  <a href="edit.php?id=<?= $vila['id'] ?>" class="btn btn-edit">
                    <i class="fas fa-edit mr-1"></i>Edit
                  </a>
                  <a href="hapus.php?type=vila&id=<?= $vila['id'] ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin menghapus vila ini?')">
                    <i class="fas fa-trash mr-1"></i>Hapus
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="empty-state text-center">
          <i class="fas fa-home-alt fa-3x mb-3"></i>
          <h3>Belum Ada Vila</h3>
          <p>Mulai tambahkan vila pertama Anda untuk memulai bisnis rental vila.</p>
          <a href="tambah.php" class="btn-gradient-primary">
            <i class="fas fa-plus mr-2"></i>Tambah Vila Pertama
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer class="text-white text-center py-3" style="background:#222;">
  <div class="container">
    <p class="mb-0">
      <i class="fas fa-heart text-danger mr-1"></i>
      &copy; 2025 JelajahVilla - Kelola vila Anda dengan mudah
    </p>
  </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
