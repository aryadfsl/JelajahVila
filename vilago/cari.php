<?php
include '../DB/koneksi.php';

$lokasi = isset($_GET['lokasi']) ? mysqli_real_escape_string($koneksi, $_GET['lokasi']) : '';
$hargaInput = isset($_GET['harga']) ? $_GET['harga'] : '';

$harga = (int) str_replace(['.', ','], '', $hargaInput);

$sql = "SELECT v.*, 
               (SELECT ROUND(AVG(rating), 1) FROM komentar WHERE komentar.id_vila = v.id) AS rata_rating
        FROM vila v
        WHERE 1=1";

if (!empty($lokasi)) {
    $sql .= " AND v.lokasi LIKE '%$lokasi%'";
}

if ($harga > 0) {
    $sql .= " AND v.harga <= $harga";
}

$sql .= " ORDER BY v.id DESC";

$query = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Hasil Pencarian Vila</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../css/cari.css">
</head>
<body>
  <div class="container py-5">
    <h3 class="mb-4">Hasil Pencarian Vila</h3>
    <div class="row">
      <?php if(mysqli_num_rows($query) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($query)) : ?>
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
                  <div class="rating-badge"><?= $row['rata_rating'] ?> ★</div>
                <?php endif; ?>
              </div>
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($row['nama']) ?></h5>
                <p class="card-text">Lokasi: <?= htmlspecialchars($row['lokasi']) ?></p>
                <p class="price-text">Rp <?= number_format($row['harga'],0,',','.') ?> / malam</p>

                <?php if ($row['rata_rating'] !== null): ?>
                  <p>
                    <?php
                      $roundedRating = round($row['rata_rating']);
                      for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $roundedRating
                          ? '<span class="star-filled">&#9733;</span>'
                          : '<span class="star-empty">&#9734;</span>';
                      }
                    ?>
                  </p>
                <?php else: ?>
                  <p>Belum ada rating</p>
                <?php endif; ?>

                <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-danger">Lihat Detail</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12">
          <p class="alert alert-warning">Maaf, vila dengan kriteria tersebut tidak ditemukan.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>