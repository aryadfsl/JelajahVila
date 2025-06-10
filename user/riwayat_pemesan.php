<?php 
include '../DB/koneksi.php'; 
session_start();

if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit;
}

$id_admin = $_SESSION['id_admin'];

// Query untuk mengambil pemesanan berdasarkan id_admin atau yang NULL (untuk backward compatibility)
$sql = "
    SELECT p.*, v.nama AS nama_vila
    FROM pemesanan p
    JOIN vila v ON p.id_vila = v.id
    WHERE p.id_admin = ? OR p.id_admin IS NULL
    ORDER BY p.id DESC
";

$stmt = $koneksi->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $koneksi->error);
}

$stmt->bind_param("i", $id_admin);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pemesanan Anda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/riwayat_pesanan.css">
    
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-history me-2"></i>Riwayat Pemesanan Anda
            </h5>
        </div>
        <div class="card-body">
            
            <?php
            // Tampilkan pesan sukses atau error
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($_SESSION['success_message']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
                unset($_SESSION['success_message']);
            }
            
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($_SESSION['error_message']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
                unset($_SESSION['error_message']);
            }
            ?>
            
            <?php if ($result->num_rows === 0): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-calendar-times fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">Belum Ada Pemesanan</h5>
                    <p class="text-muted mb-4">Anda belum melakukan pemesanan vila apapun.</p>
                    <a href="../vilago/index.php" class="btn btn-primary">Mulai Pemesanan</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>Nama Vila</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th class="text-center">Jumlah Tamu</th>
                            <th class="text-end">Total Harga</th>
                            <th class="text-center">Status Konfirmasi</th>
                            <th>Metode Pembayaran</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_vila']) ?></strong>
                                </td>
                                <td>
                                    <?= date('d M Y', strtotime($row['tanggal_checkin'])) ?>
                                </td>
                                <td>
                                    <?= date('d M Y', strtotime($row['tanggal_checkout'])) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($row['jumlah_orang']) ?> orang</span>
                                </td>
                                <td class="text-end">
                                    <strong>Rp <?= number_format($row['harga'], 0, ',', '.') ?></strong>
                                </td>

                                <td class="text-center">
                                    <?php 
                                    $status_konfirmasi = strtolower($row['status_konfirmasi']);
                                    $badge_class_konfirmasi = '';
                                    switch($status_konfirmasi) {
                                        case 'dikonfirmasi':
                                        case 'confirmed':
                                            $badge_class_konfirmasi = 'bg-success';
                                            break;
                                        case 'pending':
                                            $badge_class_konfirmasi = 'bg-warning text-dark';
                                            break;
                                        case 'ditolak':
                                        case 'rejected':
                                            $badge_class_konfirmasi = 'bg-danger';
                                            break;
                                        default:
                                            $badge_class_konfirmasi = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?= $badge_class_konfirmasi ?> status-badge">
                                        <?= htmlspecialchars($row['status_konfirmasi']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['metode_pembayaran']) ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_vila']) ?>')"
                                            title="Hapus Pemesanan">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        Total <?= $result->num_rows ?> pemesanan ditemukan
                    </small>
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="../vilago/index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0">&copy; 2025 JelajahVilla. All rights reserved.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome untuk icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pemesanan untuk:</p>
                <strong id="deleteVillaName"></strong>
                <p class="mt-2 text-muted small">Data yang sudah dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteId = null;

function confirmDelete(id, villaName) {
    deleteId = id;
    document.getElementById('deleteVillaName').textContent = villaName;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteId) {
        // Redirect ke script hapus dengan ID
        window.location.href = '../admin/hapus_pemesanan.php?id=' + deleteId;
    }
});
</script>
</body>
</html>