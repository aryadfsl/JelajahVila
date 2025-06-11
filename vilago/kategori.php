<?php
include '../DB/koneksi.php';
session_start();

if (!isset($_GET['k'])) {
    echo "Kategori tidak ditemukan.";
    exit;
}

$kategori = mysqli_real_escape_string($koneksi, $_GET['k']);

// Query untuk mengambil vila berdasarkan kategori
$query = mysqli_query($koneksi, "SELECT * FROM vila WHERE kategori = '$kategori' ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Vila Kategori <?= htmlspecialchars(ucfirst($kategori)) ?> - JelajahVilla</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../css/kategori.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="breadcrumb-nav">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> Beranda</a></li>
                        <li class="breadcrumb-item"><a href="#">Kategori</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars(ucfirst($kategori)) ?></li>
                    </ol>
                </nav>
            </div>
            
            <h1 class="hero-title">
                <i class="fas fa-home-lg-alt"></i>
                Vila <?= htmlspecialchars(ucfirst($kategori)) ?>
            </h1>
            <p class="hero-subtitle">Temukan vila terbaik untuk pengalaman menginap yang tak terlupakan</p>
        </div>
    </div>

   <!-- Main Content -->
<div class="main-content">
    <div class="container">
        <div class="text-center mb-4">
            <span class="filter-badge">
                <i class="fas fa-filter"></i>
                Kategori: <?= htmlspecialchars(ucfirst($kategori)) ?>
            </span>
        </div>

        <div class="row">
            <?php if (mysqli_num_rows($query) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($query)) : ?>
                    <?php
                        $idVila = $row['id'];
                        $fotoQuery = mysqli_query($koneksi, "SELECT nama_file FROM foto_vila WHERE id_vila = $idVila LIMIT 1");
                        $foto = mysqli_fetch_assoc($fotoQuery);
                        $gambar = $foto ? $foto['nama_file'] : 'default.jpg';

                        // Ambil rata-rata rating dari komentar
                        $ratingQuery = mysqli_query($koneksi, "SELECT AVG(rating) AS rata_rating FROM komentar WHERE id_vila = $idVila");
                        $ratingRow = mysqli_fetch_assoc($ratingQuery);
                        $rataRating = round($ratingRow['rata_rating'], 1); // Bulatkan 1 angka di belakang koma
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card villa-card h-100">
                            <div class="position-relative overflow-hidden">
                                <img src="../img/<?= htmlspecialchars($gambar) ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($row['nama']) ?>" />
                                <?php if ($rataRating): ?>
                                    <div class="rating-badge position-absolute top-0 end-0 m-2 px-2 py-1 rounded-pill d-flex align-items-center bg-warning text-white shadow-sm">
                                        <i class="fas fa-star me-1"></i>
                                        <span class="fw-bold"><?= $rataRating ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($row['nama']) ?></h5>

                                <!-- Lokasi -->
                                <p class="card-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($row['lokasi']) ?>
                                </p>

                                <!-- Harga -->
                                <p class="card-text fw-bold text-success">
                                    <i class="fas fa-tag"></i>
                                    Rp <?= number_format($row['harga'], 0, ',', '.') ?> / malam
                                </p>

                                <!-- Rating bintang -->
                                <div class="rating-stars text-orange mb-2">

                                    <?php
                                        $fullStars = floor($rataRating);
                                        $halfStar = ($rataRating - $fullStars) >= 0.5;
                                        for ($i = 0; $i < $fullStars; $i++) echo '<i class="fas fa-star"></i>';
                                        if ($halfStar) echo '<i class="fas fa-star-half-alt"></i>';
                                        for ($i = $fullStars + $halfStar; $i < 5; $i++) echo '<i class="far fa-star"></i>';
                                    ?>
                                </div>

                                <!-- Tombol Lihat Detail -->
                                <div class="mt-auto">
                                    <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-detail w-100">
                                        <i class="fas fa-eye"></i>
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="no-villas">
                        <i class="fas fa-home-heart"></i>
                        <h3>Belum Ada Vila</h3>
                        <p>Maaf, saat ini belum ada vila tersedia untuk kategori <strong><?= htmlspecialchars($kategori) ?></strong>.</p>
                        <a href="index.php" class="btn btn-detail mt-3">
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // Add loading effect to images
            const images = document.querySelectorAll('.card-img-top');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                });
            });
        });

        // Add scroll reveal animation
        window.addEventListener('scroll', () => {
            const reveals = document.querySelectorAll('.villa-card');
            
            for (let i = 0; i < reveals.length; i++) {
                const windowHeight = window.innerHeight;
                const elementTop = reveals[i].getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add('active');
                }
            }
        });
    </script>
</body>
</html>