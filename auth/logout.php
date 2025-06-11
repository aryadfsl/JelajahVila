<?php
session_start();

// Kalau sudah dikonfirmasi logout
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    session_destroy();
    header("Location: ../vilago/index.php?logout=success");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Logout</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
  Swal.fire({
    title: 'Yakin ingin logout?',
    text: "Kamu akan keluar dari akun.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, logout',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      // Arahkan ulang ke file ini tapi dengan parameter ?confirm=yes
      window.location.href = "logout.php?confirm=yes";
    } else {
      // Jika batal, kembali ke halaman sebelumnya
      window.location.href = "../vilago/index.php";
    }
  });
</script>

</body>
</html>