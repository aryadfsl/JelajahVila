<?php
session_start();

// Coba beberapa kemungkinan path untuk file koneksi
$possible_paths = [
    '../DB/koneksi.php',
    '../db/koneksi.php',
    '../../DB/koneksi.php',
    '../config/koneksi.php',
    '../includes/koneksi.php',
    'DB/koneksi.php',
    'config/koneksi.php'
];

$connection_found = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        include $path;
        $connection_found = true;
        break;
    }
}

if (!$connection_found) {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'jelajahvilla';

    $koneksi = mysqli_connect($host, $username, $password, $database);

    if (!$koneksi) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
}

$error = '';
$success = '';

if (isset($_GET['setup']) && $_GET['setup'] == 'true') {
    if (isset($koneksi)) {
        $sql = "CREATE TABLE IF NOT EXISTS admin (
            id int(11) NOT NULL AUTO_INCREMENT,
            username varchar(50) NOT NULL UNIQUE,
            password varchar(255) NOT NULL,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        )";

        if (mysqli_query($koneksi, $sql)) {
            $success = "Database berhasil disiapkan! Silakan daftar akun baru.";
        } else {
            $error = "Gagal menyiapkan database: " . mysqli_error($koneksi);
        }
    }
}

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Semua field wajib diisi!";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username hanya boleh huruf, angka, dan underscore (_).";
    } elseif (strlen($username) < 3) {
        $error = "Username minimal 3 karakter.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        if (isset($koneksi)) {
            $sql = "SELECT id FROM admin WHERE username = ?";
            $stmt = mysqli_prepare($koneksi, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    $error = "Username sudah digunakan! Silakan pilih username lain.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
                    $stmt = mysqli_prepare($koneksi, $sql);

                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);

                        if (mysqli_stmt_execute($stmt)) {
                            $success = "Registrasi berhasil! Silakan login dengan akun baru Anda.";
                            header("refresh:3;url=login.php");
                        } else {
                            $error = "Registrasi gagal: " . mysqli_error($koneksi);
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
                    }
                }
            } else {
                $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
            }
        } else {
            $error = "Koneksi database tidak tersedia.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - JelajahVilla</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>
<div class="register-container">
    <div class="register-wrapper">
        <div class="register-left">
            <div class="register-content">
                <div class="brand-logo">
                    <i class="fas fa-home"></i>
                    <span>JelajahVilla</span>
                </div>

                <div class="welcome-text">
                    <h2>Bergabung Dengan Kami!</h2>
                    <p>Daftarkan diri Anda dan nikmati kemudahan mencari vila impian untuk liburan yang tak terlupakan</p>
                </div>

                <div class="illustration">
                    <i class="fas fa-user-plus"></i>
                    <i class="fas fa-heart"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>

        <div class="register-right">
            <div class="form-container">
                <div class="form-header">
                    <h3>Buat Akun Baru</h3>
                    <p>Isi form di bawah untuk membuat akun</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success custom-alert-success" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger custom-alert" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!isset($koneksi)): ?>
                    <div class="alert alert-warning custom-alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        Database belum siap. <a href="?setup=true" class="alert-link">Klik di sini untuk setup otomatis</a>
                    </div>
                <?php endif; ?>

                <form method="POST" id="form-register" class="register-form" novalidate>
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i>
                            Username
                        </label>
                        <input type="text"
                               id="username"
                               name="username"
                               class="form-control custom-input"
                               placeholder="Masukkan username Anda"
                               required
                               autofocus
                               pattern="[a-zA-Z0-9_]+"
                               minlength="3"
                               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                        <small class="form-text text-muted">Minimal 3 karakter, hanya huruf, angka, dan underscore</small>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="password-container">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control custom-input"
                                   placeholder="Masukkan password Anda"
                                   required
                                   minlength="6">
                            <button type="button"
                                    class="password-toggle"
                                    onclick="togglePassword('password', 'toggleIcon1')"
                                    aria-label="Tampilkan/sembunyikan password">
                                <i id="toggleIcon1" class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">Minimal 6 karakter</small>
                    </div>

                    <button type="submit" name="register" class="btn-register">
                        <span>Daftar Sekarang</span>
                        <i class="fas fa-user-plus"></i>
                    </button>
                </form>

                <div class="form-footer">
                    <p>Sudah punya akun?
                        <a href="login.php" class="login-link">Login di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script src="../js/register.js"></script>

    <script>
    document.getElementById("username").addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault(); // Cegah submit form
            document.getElementById("password").focus(); // Pindah fokus ke password
        }
    });

    document.getElementById("password").addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            document.getElementById("form-login").submit(); // Submit form
        }
    });
    </script>
    <script>
function togglePassword(passwordId, iconId) {
    const passwordInput = document.getElementById(passwordId);
    const toggleIcon = document.getElementById(iconId);

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}
</script>


</body>
</html>
