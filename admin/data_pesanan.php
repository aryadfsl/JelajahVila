<?php 
include '../DB/koneksi.php'; 
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['id_admin'])) {
    header('Location: login.php');
    exit;
}

$id_admin = $_SESSION['id_admin'];

// Ambil data pemesanan yang sesuai dengan admin yang login
$sql = "
    SELECT p.*, v.nama AS nama_vila
    FROM pemesanan p
    JOIN vila v ON v.id = p.id_vila
    WHERE v.id_admin = ?
    ORDER BY p.id DESC
";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id_admin);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pemesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.9rem;
        }
        .table td {
            vertical-align: middle;
            font-size: 0.85rem;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: none;
        }
        .btn-group-vertical .btn {
            margin-bottom: 2px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-clipboard-list me-2"></i>Data Pemesanan Vila Anda
            </h5>
        </div>
        <div class="card-body">
            
            <?php
            // Tampilkan pesan sukses atau error
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                echo '<i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($_SESSION['success_message']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
                unset($_SESSION['success_message']);
            }
            
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo '<i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($_SESSION['error_message']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
                unset($_SESSION['error_message']);
            }

            if (isset($_GET['msg']) && $_GET['msg'] == 'sukses') {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                echo '<i class="fas fa-check-circle me-2"></i>Data berhasil dihapus.';
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
            }
            ?>

            <?php if ($result->num_rows === 0): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-inbox fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">Belum Ada Pemesanan</h5>
                    <p class="text-muted mb-4">Belum ada pemesanan untuk vila Anda.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>Nama Pemesan</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th class="text-center">Jumlah Tamu</th>
                            <th>Nama Vila</th>
                            <th class="text-end">Total Harga</th>
                            <th>Metode Pembayaran</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        
                        <tbody>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_pemesan']) ?></strong>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($row['email']) ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($row['telepon']) ?></small>
                                </td>
                                <td>
                                    <?= date('d M Y', strtotime($row['tanggal_checkin'])) ?>
                                </td>
                                <td>
                                    <?= date('d M Y', strtotime($row['tanggal_checkout'])) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($row['jumlah_orang']) ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_vila']) ?></strong>
                                </td>
                                <td class="text-end">
                                    <strong>Rp <?= number_format($row['harga'], 0, ',', '.') ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['metode_pembayaran']) ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $status_konfirmasi = strtolower($row['status_konfirmasi']);
                                    $badge_class = '';
                                    $status_text = $row['status_konfirmasi'];
                                    
                                    switch($status_konfirmasi) {
                                        case 'dikonfirmasi':
                                        case 'confirmed':
                                            $badge_class = 'bg-success';
                                            $status_text = 'Dikonfirmasi';
                                            break;
                                        case 'pending':
                                            $badge_class = 'bg-warning text-dark';
                                            $status_text = 'Pending';
                                            break;
                                        case 'ditolak':
                                        case 'rejected':
                                            $badge_class = 'bg-danger';
                                            $status_text = 'Ditolak';
                                            break;
                                        default:
                                            $badge_class = 'bg-secondary';
                                            $status_text = 'Belum Dikonfirmasi';
                                    }
                                    ?>
                                    <span class="badge <?= $badge_class ?> status-badge">
                                        <?= htmlspecialchars($status_text) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group-vertical" role="group">
                                        <?php if ($status_konfirmasi !== 'dikonfirmasi' && $status_konfirmasi !== 'confirmed'): ?>
                                            <button class="btn btn-success btn-sm mb-1" 
                                                    onclick="confirmBooking(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_pemesan']) ?>', '<?= htmlspecialchars($row['nama_vila']) ?>')"
                                                    title="Konfirmasi Pemesanan">
                                                <i class="fas fa-check"></i> Konfirmasi
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($status_konfirmasi !== 'ditolak' && $status_konfirmasi !== 'rejected'): ?>
                                            <button class="btn btn-warning btn-sm mb-1" 
                                                    onclick="rejectBooking(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_pemesan']) ?>', '<?= htmlspecialchars($row['nama_vila']) ?>')"
                                                    title="Tolak Pemesanan">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="deleteBooking(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_pemesan']) ?>', '<?= htmlspecialchars($row['nama_vila']) ?>')"
                                                title="Hapus Pemesanan">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Total <?= $result->num_rows ?> pemesanan ditemukan
                    </small>
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Konfirmasi Aksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                <!-- Content will be filled by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Konfirmasi</button>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0">&copy; 2025 JelajahVilla. All rights reserved.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let currentAction = null;
let currentId = null;

function confirmBooking(id, namaPemesan, namaVila) {
    currentAction = 'confirm';
    currentId = id;
    
    document.getElementById('confirmModalTitle').innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>Konfirmasi Pemesanan';
    document.getElementById('confirmModalBody').innerHTML = `
        <p>Apakah Anda yakin ingin <strong class="text-success">mengkonfirmasi</strong> pemesanan ini?</p>
        <div class="alert alert-info">
            <strong>Detail Pemesanan:</strong><br>
            <i class="fas fa-user me-1"></i> Pemesan: ${namaPemesan}<br>
            <i class="fas fa-home me-1"></i> Vila: ${namaVila}
        </div>
        <p class="text-muted small">Setelah dikonfirmasi, status pemesanan akan berubah menjadi "Dikonfirmasi".</p>
    `;
    document.getElementById('confirmActionBtn').className = 'btn btn-success';
    document.getElementById('confirmActionBtn').innerHTML = '<i class="fas fa-check me-1"></i>Ya, Konfirmasi';
    
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

function rejectBooking(id, namaPemesan, namaVila) {
    currentAction = 'reject';
    currentId = id;
    
    document.getElementById('confirmModalTitle').innerHTML = '<i class="fas fa-times-circle text-warning me-2"></i>Tolak Pemesanan';
    document.getElementById('confirmModalBody').innerHTML = `
        <p>Apakah Anda yakin ingin <strong class="text-warning">menolak</strong> pemesanan ini?</p>
        <div class="alert alert-warning">
            <strong>Detail Pemesanan:</strong><br>
            <i class="fas fa-user me-1"></i> Pemesan: ${namaPemesan}<br>
            <i class="fas fa-home me-1"></i> Vila: ${namaVila}
        </div>
        <p class="text-muted small">Setelah ditolak, status pemesanan akan berubah menjadi "Ditolak".</p>
    `;
    document.getElementById('confirmActionBtn').className = 'btn btn-warning';
    document.getElementById('confirmActionBtn').innerHTML = '<i class="fas fa-times me-1"></i>Ya, Tolak';
    
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

function deleteBooking(id, namaPemesan, namaVila) {
    currentAction = 'delete';
    currentId = id;
    
    document.getElementById('confirmModalTitle').innerHTML = '<i class="fas fa-trash text-danger me-2"></i>Hapus Pemesanan';
    document.getElementById('confirmModalBody').innerHTML = `
        <p>Apakah Anda yakin ingin <strong class="text-danger">menghapus</strong> pemesanan ini?</p>
        <div class="alert alert-danger">
            <strong>Detail Pemesanan:</strong><br>
            <i class="fas fa-user me-1"></i> Pemesan: ${namaPemesan}<br>
            <i class="fas fa-home me-1"></i> Vila: ${namaVila}
        </div>
        <p class="text-muted small"><strong>Peringatan:</strong> Data yang sudah dihapus tidak dapat dikembalikan.</p>
    `;
    document.getElementById('confirmActionBtn').className = 'btn btn-danger';
    document.getElementById('confirmActionBtn').innerHTML = '<i class="fas fa-trash me-1"></i>Ya, Hapus';
    
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

document.getElementById('confirmActionBtn').addEventListener('click', function() {
    if (currentId && currentAction) {
        switch(currentAction) {
            case 'confirm':
                window.location.href = 'proses_konfirmasi.php?action=confirm&id=' + currentId;
                break;
            case 'reject':
                window.location.href = 'proses_konfirmasi.php?action=reject&id=' + currentId;
                break;
            case 'delete':
                window.location.href = 'hapus_pemesanan.php?id=' + currentId;
                break;
        }
    }
});
</script>

</body>
</html>