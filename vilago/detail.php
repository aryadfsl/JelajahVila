<?php
include '../DB/koneksi.php';
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

if (isset($_GET['pesan']) && $_GET['pesan'] === 'success') {
  echo "<script>alert('Pemesanan berhasil! Terima kasih telah memesan.');</script>";
}

if (!isset($_GET['id'])) {
    echo "Vila tidak ditemukan.";
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data vila
$sql = "SELECT * FROM vila WHERE id = $id";
$result = mysqli_query($koneksi, $sql);
$vila = mysqli_fetch_assoc($result);
if (!$vila) {
    echo "Data vila tidak tersedia.";
    exit;
}

// Ambil foto vila
$fotos = [];
$cover = null;
$sqlFoto = "SELECT nama_file, is_cover FROM foto_vila WHERE id_vila = $id ORDER BY is_cover DESC, id ASC";
$resultFoto = mysqli_query($koneksi, $sqlFoto);
while ($row = mysqli_fetch_assoc($resultFoto)) {
    $ext = strtolower(pathinfo($row['nama_file'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($ext, $allowed_ext)) continue; // skip kalau bukan file gambar

    if ($row['is_cover']) {
        $cover = $row['nama_file'];
    } else {
        $fotos[] = $row['nama_file'];
    }
}
if ($cover) {
    array_unshift($fotos, $cover);
}

// Ambil fasilitas vila
$sqlFasilitas = "SELECT nama_fasilitas FROM fasilitas_vila WHERE id_vila = $id";
$resultFasilitas = mysqli_query($koneksi, $sqlFasilitas);

// Ambil komentar vila
$sqlKomentar = "SELECT * FROM komentar WHERE id_vila = $id ORDER BY tanggal DESC";
$resultKomentar = mysqli_query($koneksi, $sqlKomentar);

// Ambil tanggal-tanggal yang sudah dipesan untuk disable di datepicker
$tanggal_dipesan = [];
$sqlPemesanan = "SELECT tanggal_checkin, tanggal_checkout FROM pemesanan WHERE id_vila = $id";
$resultPemesanan = mysqli_query($koneksi, $sqlPemesanan);
while ($p = mysqli_fetch_assoc($resultPemesanan)) {
    $start = strtotime($p['tanggal_checkin']);
    $end = strtotime($p['tanggal_checkout']);
    for ($ts = $start; $ts <= $end; $ts += 86400) {
        $tanggal_dipesan[] = date('Y-m-d', $ts);
    }
}
$tanggal_dipesan = array_unique($tanggal_dipesan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($vila['nama']) ?> - JelajahVilla</title>

  <!-- Bootstrap CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
  
  <!-- Bootstrap Datepicker CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet" />
  
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/detail.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-home"></i> JelajahVilla
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item"><a class="nav-link" href="#info"><i class="fas fa-info-circle"></i> Info</a></li>
          <li class="nav-item"><a class="nav-link" href="#tentang"><i class="fas fa-file-alt"></i> Deskripsi</a></li>
          <li class="nav-item"><a class="nav-link" href="#fasilitas"><i class="fas fa-list"></i> Fasilitas</a></li>
          <li class="nav-item"><a class="nav-link" href="#pesan"><i class="fas fa-calendar-alt"></i> Pesan</a></li>
          <li class="nav-item"><a class="nav-link" href="#kontak"><i class="fas fa-comments"></i> Review</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="hero-section" id="info">
  <div class="container">
    <!-- Tombol Kembali ke Index -->
    <div class="mb-3 text-start">
  <a href="<?= htmlspecialchars($referrer) ?>" class="btn btn-gradient">
    <i class="fas fa-arrow-left me-2"></i> Kembali 
  </a>
</div>
      <h1 class="villa-title text-center"><?= htmlspecialchars($vila['nama']) ?></h1
      <?php if (count($fotos) > 0): ?>
  <div class="carousel-container">
    <div id="carouselFotoVila" class="carousel slide" data-ride="carousel">
      <div class="carousel-inner">
        <?php foreach ($fotos as $index => $foto): ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <img src="../img/<?= htmlspecialchars($foto) ?>" alt="Foto Vila" class="d-block w-100" />
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($fotos) > 1): ?>
        <a class="carousel-control-prev" href="#carouselFotoVila" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </a>
        <a class="carousel-control-next" href="#carouselFotoVila" role="button" data-slide="next">
          <span class="carousel-control-next-icon"></span>
        </a>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <div class="content-card text-center">
    <i class="fas fa-image fa-3x text-muted mb-3"></i>
    <p class="text-muted">Belum ada foto untuk vila ini.</p>
  </div>
<?php endif; ?>

      <div class="row">
        <!-- Kolom Kiri -->
        <div class="col-lg-8">
          <div class="content-card" id="tentang">
            <h4><i class="fas fa-info-circle"></i> Deskripsi Villa</h4>
            <p class="lead"><?= nl2br(htmlspecialchars($vila['deskripsi'])) ?></p>
          </div>

          <div class="content-card" id="fasilitas">
            <h4><i class="fas fa-star"></i> Fasilitas Premium</h4>
            <?php if (mysqli_num_rows($resultFasilitas) > 0): ?>
              <div class="facilities-grid">
                <?php 
                $facility_icons = [
                  'WiFi' => 'fas fa-wifi',
                  'Kolam Renang' => 'fas fa-swimmer',
                  'Parkir' => 'fas fa-parking',
                  'Dapur' => 'fas fa-utensils',
                  'AC' => 'fas fa-snowflake',
                  'TV' => 'fas fa-tv',
                  'Kamar Mandi' => 'fas fa-bath',
                  'Balkon' => 'fas fa-home'
                ];
                
                while ($fas = mysqli_fetch_assoc($resultFasilitas)) : 
                  $icon = 'fas fa-check-circle';
                  foreach ($facility_icons as $name => $fa_icon) {
                    if (stripos($fas['nama_fasilitas'], $name) !== false) {
                      $icon = $fa_icon;
                      break;
                    }
                  }
                ?>
                  <div class="facility-item">
                    <i class="<?= $icon ?>"></i>
                    <span><?= htmlspecialchars($fas['nama_fasilitas']) ?></span>
                  </div>
                <?php endwhile; ?>
              </div>
            <?php else: ?>
              <p class="text-muted"><em>Belum ada data fasilitas untuk vila ini.</em></p>
            <?php endif; ?>
          </div>

          <div class="content-card">
            <h4><i class="fas fa-map-marker-alt"></i> Informasi Detail</h4>
            <div class="info-grid">
              <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <strong>Lokasi</strong>
                <span><?= htmlspecialchars($vila['lokasi']) ?></span>
              </div>
              <div class="info-item">
                <i class="fas fa-money-bill-wave"></i>
                <strong>Harga</strong>
                <span>Rp<?= number_format($vila['harga'], 0, ',', '.') ?> / malam</span>
              </div>
              <div class="info-item">
                <i class="fas fa-phone"></i>
                <strong>Kontak</strong>
                <span><?= htmlspecialchars($vila['kontak']) ?></span>
              </div>
              <div class="info-item">
                <i class="fas fa-envelope"></i>
                <strong>Email</strong>
                <span><?= htmlspecialchars($vila['email']) ?></span>
              </div>
            </div>
          </div>

          <?php if (!empty($vila['maps_embed'])): ?>
            <div class="content-card">
              <h4><i class="fas fa-map"></i> Lokasi di Google Maps</h4>
              <div class="maps-container">
                <div class="embed-responsive embed-responsive-16by9">
                  <?= $vila['maps_embed'] ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Kolom Kanan -->
        <div class="col-lg-4">
          <div class="booking-card" id="pesan">
            <div class="text-center mb-4">
              <div class="price-display" id="hargaTotal">Rp<?= number_format($vila['harga'], 0, ',', '.') ?></div>
              <small class="text-muted">per malam</small>
            </div>
            
            <form action="../user/pesan.php" method="POST" id="bookingForm">
              <input type="hidden" name="id_vila" value="<?= $id ?>">

              <div class="form-group">
                <label for="checkin"><i class="fas fa-calendar-check"></i> Check-in</label>
                <input type="text" id="checkin" name="checkin" class="form-control" autocomplete="off" required>
                <div class="validation-message" id="checkinError">Tanggal check-in tidak valid</div>
              </div>
              
              <div class="form-group">
                <label for="checkout"><i class="fas fa-calendar-times"></i> Check-out</label>
                <input type="text" id="checkout" name="checkout" class="form-control" autocomplete="off" required>
                <div class="validation-message" id="checkoutError">Tanggal check-out tidak valid</div>
              </div>
              
                      <div class="form-group">
          <label for="tamu"><i class="fas fa-users"></i> Jumlah Tamu</label>
          <select name="tamu" id="tamu" class="form-control" required>
            <?php
              for ($i = 1; $i <= 10; $i++) {
                echo "<option value=\"$i\">$i tamu</option>";
              }
            ?>
          </select>
        </div>

              
              <button type="submit" class="btn btn-book btn-block" id="submitBtn">
                <i class="fas fa-calendar-plus"></i> Pesan Sekarang
              </button>
              <small class="text-muted d-block text-center mt-3">
                <i class="fas fa-shield-alt"></i> Pembayaran aman & terpercaya
              </small>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Komentar Section -->
  <div class="container" id="kontak">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Komentar berhasil dikirim!
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'pesan_success'): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Pemesanan berhasil dibuat! Terima kasih.
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> 
            <?php
              switch($_GET['error']) {
                case 'tanggal_conflict':
                  echo 'Tanggal yang dipilih sudah dipesan. Silakan pilih tanggal lain.';
                  break;
                case 'invalid_date':
                  echo 'Tanggal check-out harus setelah tanggal check-in.';
                  break;
                case 'past_date':
                  echo 'Tidak dapat memesan tanggal yang sudah berlalu.';
                  break;
                default:
                  echo 'Terjadi kesalahan. Silakan coba lagi.';
              }
            ?>
          </div>
        <?php endif; ?>

        <div class="comment-form">
          <h4><i class="fas fa-edit"></i> Tulis Review Anda</h4>
          
          <form action="../user/komentar.php" method="POST">
            <input type="hidden" name="id_vila" value="<?= $id ?>">
            <input type="hidden" name="rating" id="ratingKomentarInput" required>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="nama"><i class="fas fa-user"></i> Nama Lengkap</label>
                  <input type="text" class="form-control" id="nama" name="nama" required placeholder="Nama Anda...">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="email"><i class="fas fa-envelope"></i> Email</label>
                  <input type="email" class="form-control" id="email" name="email" required placeholder="nama@email.com">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="pesan"><i class="fas fa-comment"></i> Review Anda</label>
              <textarea class="form-control" id="pesan" name="pesan" rows="4" required placeholder="Ceritakan pengalaman Anda di villa ini..."></textarea>
            </div>

            <div class="form-group">
              <label><i class="fas fa-star"></i> Rating</label>
              <div id="starRating" class="mb-3">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
              </div>
            </div>
            
            <div class="text-right">
              <button type="submit" class="btn btn-book">
                <i class="fas fa-paper-plane"></i> Kirim Review
              </button>
            </div>
          </form>
        </div>

        <div class="content-card">
          <h4><i class="fas fa-comments"></i> Review Pengunjung</h4>
          
          <?php if (mysqli_num_rows($resultKomentar) > 0): ?>
            <?php while ($komen = mysqli_fetch_assoc($resultKomentar)) : ?>
              <div class="comment-item">
                <div class="comment-header">
                  <div class="comment-avatar">
                    <?= strtoupper(substr($komen['nama'], 0, 1)) ?>
                  </div>
                  <div>
                    <strong><?= htmlspecialchars($komen['nama']) ?></strong>
                    <br>
                    <small class="text-muted">
                      <i class="fas fa-calendar"></i> 
                      <?= date('d M Y, H:i', strtotime($komen['tanggal'])) ?>
                    </small>
                  </div>
                </div>

                <div class="comment-stars">
                  <?php
                    $rating = intval($komen['rating']);
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $rating) {
                            echo '<i class="fas fa-star" style="color: gold;"></i>';
                        } else {
                            echo '<i class="far fa-star" style="color: #ddd;"></i>';
                        }
                    }
                  ?>
                </div>

                <p class="mb-0"><?= nl2br(htmlspecialchars($komen['pesan'])) ?></p>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
              <p class="text-muted">Belum ada review untuk villa ini. Jadilah yang pertama!</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>




  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h5><i class="fas fa-home"></i> JelajahVilla</h5>
          <p>Platform terpercaya untuk menyewa villa impian Anda</p>
        </div>
        <div class="col-md-6 text-md-right">
          <div class="social-links">
            <a href="#" class="text-white mr-3"><i class="fab fa-facebook fa-2x"></i></a>
            <a href="#" class="text-white mr-3"><i class="fab fa-instagram fa-2x"></i></a>
            <a href="#" class="text-white mr-3"><i class="fab fa-twitter fa-2x"></i></a>
          </div>
          <p class="mt-3">&copy; 2025 JelajahVilla. All rights reserved.</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Bootstrap Datepicker JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.id.min.js"></script>

  <script>
    // Smooth scrolling untuk navigation links
    $(document).ready(function() {
      $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
          event.preventDefault();
          $('html, body').stop().animate({
            scrollTop: target.offset().top - 100
          }, 1000);
        }
      });
    });



    // Enhanced navbar scroll effect
    $(window).scroll(function() {
      if ($(window).scrollTop() > 50) {
        $('.navbar').addClass('scrolled');
      } else {
        $('.navbar').removeClass('scrolled');
      }
    });

    // Datepicker functionality
    $(function() {
      var disabledDates = <?= json_encode($tanggal_dipesan) ?>;

      function disableDates(date) {
        var d = date.getFullYear() + '-' +
                ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                ('0' + date.getDate()).slice(-2);
        return disabledDates.indexOf(d) === -1;
      }

      $('#checkin').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        startDate: new Date(),
        language: 'id',
        beforeShowDay: disableDates
      }).on('changeDate', function(e) {
        var checkinDate = e.date;
        $('#checkout').datepicker('setStartDate', new Date(checkinDate.getTime() + 86400000));
        
        var checkoutVal = $('#checkout').datepicker('getDate');
        if (!checkoutVal || checkoutVal <= checkinDate) {
          $('#checkout').datepicker('setDate', new Date(checkinDate.getTime() + 86400000));
        }
        hitungHarga();
      });

      $('#checkout').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        startDate: new Date(new Date().getTime() + 86400000),
        language: 'id',
        beforeShowDay: disableDates
      }).on('changeDate', function() {
        hitungHarga();
      });

      function hitungHarga() {
        var hargaMalam = <?= (int)$vila['harga'] ?>;
        var checkin = $('#checkin').datepicker('getDate');
        var checkout = $('#checkout').datepicker('getDate');
        if (checkin && checkout && checkout > checkin) {
          var diff = Math.ceil((checkout - checkin) / (1000 * 3600 * 24));
          var total = diff * hargaMalam;
          $('#hargaTotal').html('Rp' + total.toLocaleString('id-ID') + '<br><small class="text-muted">untuk ' + diff + ' malam</small>');
        } else {
          $('#hargaTotal').html('Rp' + (hargaMalam * 1).toLocaleString('id-ID') + '<br><small class="text-muted">per malam</small>');
        }
      }

      var hariIni = new Date();
      $('#checkin').datepicker('setDate', hariIni);
      $('#checkout').datepicker('setDate', new Date(hariIni.getTime() + 86400000));
      hitungHarga();
    });

    // Enhanced star rating system
    document.addEventListener('DOMContentLoaded', function () {
      const stars = document.querySelectorAll('#starRating .star');
      const ratingInput = document.getElementById('ratingKomentarInput');
      let currentRating = 0;



      stars.forEach((star, index) => {
        star.addEventListener('mouseenter', function () {
          highlightStars(index + 1);
        });

        star.addEventListener('mouseleave', function () {
          highlightStars(currentRating);
        });

        star.addEventListener('click', function () {
          currentRating = index + 1;
          ratingInput.value = currentRating;
          highlightStars(currentRating);
          
          // Add animation effect
          star.style.transform = 'scale(1.3)';
          setTimeout(() => {
            star.style.transform = 'scale(1.1)';
          }, 150);
        });
      });

      function highlightStars(rating) {
        stars.forEach((star, index) => {
          if (index < rating) {
            star.classList.add('selected');
          } else {
            star.classList.remove('selected');
          }
        });
      }
    });

    // Loading animation for booking button
    $('form').on('submit', function() {
      const submitBtn = $(this).find('button[type="submit"]');
      const originalText = submitBtn.html();
      submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
      submitBtn.prop('disabled', true);
      
      // Re-enable after 3 seconds in case of error
      setTimeout(() => {
        submitBtn.html(originalText);
        submitBtn.prop('disabled', false);
      }, 3000);
    });

    // Parallax effect for hero section
    $(window).scroll(function() {
      var scrollTop = $(window).scrollTop();
      $('.carousel-container').css('transform', 'translateY(' + scrollTop * 0.1 + 'px)');
    });

    // Animate elements on scroll
    function animateOnScroll() {
      $('.content-card, .comment-item').each(function() {
        var elementTop = $(this).offset().top;
        var elementBottom = elementTop + $(this).outerHeight();
        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop + $(window).height();

        if (elementBottom > viewportTop && elementTop < viewportBottom) {
          $(this).addClass('animate__animated animate__fadeInUp');
        }
      });
    }

    $(window).on('scroll', animateOnScroll);
    $(document).ready(animateOnScroll);

    // Price calculation animation
    function animatePrice() {
      const priceElement = $('#hargaTotal');
      priceElement.addClass('animate__animated animate__pulse');
      setTimeout(() => {
        priceElement.removeClass('animate__animated animate__pulse');
      }, 1000);
    }

    // Image lazy loading for performance
    $(document).ready(function() {
      $('img').each(function() {
        $(this).on('load', function() {
          $(this).addClass('loaded');
        });
      });
    });

    // Tooltip initialization
    $(function () {
      $('[data-toggle="tooltip"]').tooltip();
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
      $('.alert').fadeOut('slow');
    }, 5000);
  </script>

  
</body>
</html>