<?php
include '../DB/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_vila = intval($_POST['id_vila']);
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $tamu = intval($_POST['tamu']);

    $query = mysqli_query($koneksi, "SELECT * FROM vila WHERE id = $id_vila");
    $vila = mysqli_fetch_assoc($query);

    if (!$vila) {
        die("Vila tidak ditemukan.");
    }

    $start = new DateTime($checkin);
    $end = new DateTime($checkout);
    $interval = $start->diff($end)->days;

    if ($interval <= 0) {
        die("Tanggal check-out harus setelah check-in.");
    }

    $harga_total = $vila['harga'] * $interval;
    $diskon = isset($_POST['diskon']) ? intval($_POST['diskon']) : 0;
    $diskon = max(0, min(100, $diskon)); // pastikan antara 0-100
    $harga_setelah_diskon = $harga_total - ($harga_total * $diskon / 100);

} else {
    header("Location: ../admin/riwayar_pemesan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Form Pemesanan Vila</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/pesan.css" />
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-home me-2"></i>Form Pemesanan Vila</h3>
                    </div>
                    <div class="card-body">
                        <form method="post" action="../vilago/konfirmasi.php" enctype="multipart/form-data">
                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-4">
                                    <div class="price-box">
                                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                        <h4>Rp <?= number_format($harga_total, 0, ',', '.') ?></h4>
                                        <small>Total Pembayaran</small>
                                    </div>

                                    <div class="payment-box">
                                        <label class="form-label">
                                            <i class="fas fa-credit-card me-1"></i>Metode Pembayaran
                                        </label>
                                        <select name="metode_pembayaran" class="form-select" required>
                                            <option value="">-- Pilih Metode --</option>
                                            <option value="Transfer Bank">💳 Transfer Bank</option>
                                            <option value="COD">💰 Bayar di Tempat (COD)</option>
                                        </select>

                                        <!-- Info Rekening, tampil kalau Transfer Bank -->
                                        <div id="infoRekening" style="display:none; margin-top: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                                            <h6>Informasi Rekening Pembayaran:</h6>
                                            <p><strong>Nama Pemilik Rekening:</strong> <?= htmlspecialchars($vila['nama_pemilik']) ?></p>
                                            <p><strong>Bank:</strong> <?= htmlspecialchars($vila['bank']) ?></p>
                                            <p><strong>Nomor Rekening:</strong> <?= htmlspecialchars($vila['no_rekening']) ?></p>

                                            <label for="bukti_transfer" class="form-label mt-3">Upload Bukti Transfer:</label>
                                            <input type="file" name="bukti_transfer" id="bukti_transfer" class="form-control" accept="image/*" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-8">
                                    <!-- Hidden Fields -->
                                    <input type="hidden" name="id_vila" value="<?= $id_vila ?>" />
                                    <input type="hidden" name="checkin" value="<?= htmlspecialchars($checkin) ?>" />
                                    <input type="hidden" name="checkout" value="<?= htmlspecialchars($checkout) ?>" />
                                    <input type="hidden" name="tamu" value="<?= $tamu ?>" />
                                    <input type="hidden" name="harga" value="<?= $harga_total ?>" />

                                    <!-- Info Pemesanan -->
                                    <div class="info-box">
                                        <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Detail Pemesanan</h6>
                                        <div class="info-row">
                                            <span><strong>Vila:</strong></span>
                                            <span><?= htmlspecialchars($vila['nama']) ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span><strong>Check-in:</strong></span>
                                            <span><?= htmlspecialchars($checkin) ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span><strong>Check-out:</strong></span>
                                            <span><?= htmlspecialchars($checkout) ?></span>
                                        </div>
                                        <div class="info-row">
                                            <span><strong>Tamu:</strong></span>
                                            <span><?= $tamu ?> orang</span>
                                        </div>
                                    </div>

                                    <!-- Data Pemesan -->
                                    <h6 class="mb-3"><i class="fas fa-user me-2"></i>Data Pemesan</h6>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" name="nama" class="form-control" required />
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nomor Telepon</label>
                                            <input type="text" name="telepon" class="form-control" required />
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required />
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>Pesan Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        &copy; 2025 JelajahVilla - Booking Vila Terpercaya
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const metodePembayaran = document.querySelector('select[name="metode_pembayaran"]');
        const infoRekeningDiv = document.getElementById('infoRekening');
        const buktiTransferInput = document.getElementById('bukti_transfer');

        metodePembayaran.addEventListener('change', function () {
            if (this.value === 'Transfer Bank') {
                infoRekeningDiv.style.display = 'block';
                buktiTransferInput.setAttribute('required', 'required');
            } else {
                infoRekeningDiv.style.display = 'none';
                buktiTransferInput.removeAttribute('required');
                buktiTransferInput.value = '';
            }
        });
    </script>
</body>
</html>
