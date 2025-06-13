<?php
include '../DB/koneksi.php';
session_start();

// Query ambil data vila dan rata-rata rating, tanpa join diskon
$sql = "SELECT v.*, 
               (SELECT ROUND(AVG(rating), 1) FROM komentar WHERE komentar.id_vila = v.id) AS rata_rating
        FROM vila v
        ORDER BY v.id DESC";

$result = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>JelajahVilla - Booking</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/index.css">
</head>
<body id="top">


<nav class="navbar navbar-expand-lg navbar-light fixed-top">
  <div class="container">
    <a class="navbar-brand" href="#">
      <img src="../img/foto logo.png" alt="JelajahVilla" width="30" class="d-inline-block align-top" />
      JelajahVila
    </a>

    <div class="ml-auto d-flex align-items-center">
      <?php if (isset($_SESSION['admin'])): ?>
        <span class="mr-3 font-weight-500">
          <i class="fas fa-user-shield mr-1"></i>
          Hallo, <?= htmlspecialchars($_SESSION['admin']); ?>!
        </span>

        <?php if (isset($_SESSION['user'])): ?>
        <a href="../user/riwayat_pemesan.php" class="btn btn-outline-info mr-2">
          <i class="fas fa-history mr-1"></i>Pesanan Saya
        </a>
      <?php endif; ?>
      
        <a href="../admin/dashboard.php" class="btn btn-outline-primary mr-2">
          <i class="fas fa-plus-circle mr-1"></i>Daftarkan Vila Anda
        </a>
        <a href="#cari-vila" class="btn btn-success mr-2">
          <i class="fas fa-search mr-1"></i>Cari Vila
        </a>
        <a href="../auth/logout.php?logout=true" class="btn btn-danger">
          <i class="fas fa-sign-out-alt mr-1"></i>Logout
        </a>

      <?php elseif (isset($_SESSION['user'])): ?>
        <span class="mr-3 font-weight-500">
          <i class="fas fa-user mr-1"></i>
          Hallo, <?= htmlspecialchars($_SESSION['user']); ?>!
        </span>
        <a href="../user/riwayat_pemesan.php" class="btn btn-outline-info mr-2">
          <i class="fas fa-history mr-1"></i>Pesanan Saya
        </a>
        <a href="#cari-vila" class="btn btn-success mr-2">
          <i class="fas fa-search mr-1"></i>Cari Vila
        </a>
        <a href="../auth/logout.php?logout=true" class="btn btn-danger">
          <i class="fas fa-sign-out-alt mr-1"></i>Logout
        </a>

      <?php else: ?>
        <a href="#cari-vila" class="btn btn-success mr-2">
          <i class="fas fa-search mr-1"></i>Cari Vila
        </a>
        <a href="../auth/login.php" onclick="alert('Silakan login terlebih dahulu untuk mendaftarkan vila.')" class="btn btn-outline-danger mr-2">
          <i class="fas fa-home mr-1"></i>Daftarkan Vila Anda
        </a>
        <a href="../auth/login.php" class="btn btn-outline-primary">
          <i class="fas fa-sign-in-alt mr-1"></i>Masuk
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="container hero-content text-white">
    <div class="row align-items-center">
      <div class="col-md-6">
        <h1 class="font-weight-bold">
          Sewa Vila,<br />
          atau Cobain Nginep Seru di <span class="highlight">JelajahVila</span>
        </h1>
        <p class="lead mb-4" style="font-size: 1.2rem; opacity: 0.9;">
          Temukan pengalaman menginap yang tak terlupakan dengan vila-vila terbaik pilihan kami
        </p>
      </div>
      <div class="col-md-5 offset-md-1" id="cari-vila">
        <form action="cari.php" method="GET" class="search-box">
          <h4 class="text-white mb-3 text-center font-weight-600">
            <i class="fas fa-map-marker-alt mr-2"></i>Cari Vila Impianmu
          </h4>
          <div class="form-group">
            <input type="text" name="lokasi" class="form-control" placeholder="🏖️ Mau nginep di mana?" />
          </div>
          <div class="form-row">
            <div class="col">
              <input type="number" name="harga" class="form-control" placeholder="💰 Cari Harga yuk!!" id="harga" oninput="validasiAngka()" />
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block mt-3">
            <i class="fas fa-search mr-2"></i>Ayo Cari Sekarang!
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<section class="category-section py-5">
  <div class="container d-flex justify-content-around flex-wrap">
    <a href="kategori.php?k=pantai" class="text-center text-decoration-none text-dark">
      <div class="category-icon">
        <img src="../img/pantai.jpg" alt="Pantai" />
        <p><i class="fas fa-umbrella-beach mr-1"></i>Pantai</p>
      </div>
    </a>
    <a href="kategori.php?k=pegunungan" class="text-center text-decoration-none text-dark">
      <div class="category-icon">
        <img src="../img/pengunungan.jpg" alt="Pegunungan" />
        <p><i class="fas fa-mountain mr-1"></i>Pegunungan</p>
      </div>
    </a>
    <a href="kategori.php?k=tengah kota" class="text-center text-decoration-none text-dark">
      <div class="category-icon">
        <img src="../img/tengah kota.jpg" alt="Tengah Kota" />
        <p><i class="fas fa-city mr-1"></i>Tengah Kota</p>
      </div>
    </a>
  </div>
</section>

<section class="villa-section">
  <div class="container">
    <h3 class="mb-5">
      <i class="fas fa-star mr-2" style="color: #ffd700;"></i>
      Rekomendasi Vila Untukmu
    </h3>
    <div class="row">
      <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <?php
          $idVila = $row['id'];
          $fotoQuery = mysqli_query($koneksi, "SELECT nama_file FROM foto_vila WHERE id_vila = $idVila LIMIT 1");
          $foto = mysqli_fetch_assoc($fotoQuery);
          $gambar = $foto ? $foto['nama_file'] : 'default.jpg';
        ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="position-relative">
              <img src="../img/<?= htmlspecialchars($gambar) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['nama']) ?>" />
              <?php if ($row['rata_rating'] !== null): ?>
                <div class="rating-badge">
                  <i class="fas fa-star mr-1"></i><?= $row['rata_rating'] ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <h5 class="card-title">
                <i class="fas fa-home mr-2" style="color: #667eea;"></i>
                <?= htmlspecialchars($row['nama']) ?>
              </h5>
              <p class="card-text">
                <i class="fas fa-map-marker-alt mr-2" style="color: #e74c3c;"></i>
                <?= htmlspecialchars($row['lokasi']) ?>
              </p>
              
              <!-- Harga tanpa diskon -->
              <p class="card-text font-weight-bold" style="color: #27ae60; font-size: 1.3rem;">
                <i class="fas fa-tag mr-2"></i>
                Rp <?= number_format($row['harga'],0,',','.') ?> / malam
              </p>

              <?php if ($row['rata_rating'] !== null): ?>
                <div class="mb-3">
                  <?php
                    $roundedRating = round($row['rata_rating']);
                    for ($i = 1; $i <= 5; $i++) {
                      if ($i <= $roundedRating) {
                        echo '<span class="star-filled">&#9733;</span>';
                      } else {
                        echo '<span class="star-empty">&#9734;</span>';
                      }
                    }
                  ?>
                </div>
              <?php else: ?>
                <p class="text-muted">
                  <i class="fas fa-star-half-alt mr-1"></i>
                  Belum ada rating
                </p>
              <?php endif; ?>

              <?php if (isset($_SESSION['user'])): ?>
                <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-block">
                  <i class="fas fa-eye mr-2"></i>Lihat Detail
                </a>
              <?php else: ?>
                <a href="../auth/login.php" onclick="alert('Silakan login terlebih dahulu untuk melihat detail vila.');" class="btn btn-primary btn-block">
                  <i class="fas fa-sign-in-alt mr-2"></i>Login untuk Lihat Detail
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<section class="cta-section">
  <div class="container text-center">
    <h3 class="mb-4 font-weight-bold">
      <i class="fas fa-handshake mr-3"></i>
      Yuk, Sewakan Akomodasi & Penginapanmu di JelajahVila
    </h3>
    <p class="lead max-width-800 mx-auto">
      Lebarkan potensi dan peluang dengan menjadi partner <strong>JelajahVila</strong>! Daftarkan properti kamu dan dapatkan pemasaran luas serta pertumbuhan bisnis penginapan yang maksimal.
      Akomodasi kamu akan menjangkau pencari dari seluruh dunia. Selain itu, kamu bisa menganalisis statistik properti secara berkala dan mengatur allotment untuk memaksimalkan pendapatan.
      Tunggu apalagi? Yuk, sewakan penginapanmu sekarang juga dan jadilah bagian dari jaringan partner kami!
    </p>
    <div class="mt-4">
    <?php if (isset($_SESSION['user']) || isset($_SESSION['admin'])): ?>
  <a href="#top" class="btn btn-light btn-lg mr-3">
    <i class="fas fa-rocket mr-2"></i>Mulai Sekarang
  </a>
<?php else: ?>
  <a href="../auth/login.php" onclick="alert('Silakan login terlebih dahulu untuk menyewakan penginapan.')" class="btn btn-light btn-lg mr-3">
    <i class="fas fa-rocket mr-2"></i>Mulai Sekarang
  </a>
<?php endif; ?>

    </div>
  </div>
</section>

<footer class="text-center">
  <div class="container">
    <p class="mb-0">
      &copy; 2025 JelajahVila - Dibuat untuk pengalaman terbaik Anda
    </p>
  </div>
</footer>

<script>
  //
  // Validasi form cari vila supaya minimal isi lokasi atau harga
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.search-box');
    if (form) {
      form.addEventListener('submit', function (e) {
        const lokasi = form.querySelector('input[name="lokasi"]').value.trim();
        const harga = form.querySelector('input[name="harga"]').value.trim();

        if (lokasi === '' && harga === '') {
          alert('Silakan isi minimal salah satu: lokasi atau harga.');
          e.preventDefault(); // cegah submit form
        }
      });
    }
  });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
  <script>
    alert("Anda telah berhasil logout.");
  </script>
<?php endif; ?>

</body>
</html>
