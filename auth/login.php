<?php
session_start();
include '../DB/koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username'");
    $data  = mysqli_fetch_assoc($query);

    if ($data && password_verify($password, $data['password'])) {
        $_SESSION['username']  = $data['username'];
        $_SESSION['admin']     = $data['username'];
        $_SESSION['user']      = $data['username'];
        $_SESSION['id_admin']  = $data['id'];

        header("Location:../vilago/index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JelajahVilla</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-wrapper">
            <!-- Left Side - Image/Illustration -->
            <div class="login-left">
                <div class="login-content">
                    <div class="brand-logo">
                        <i class="fas fa-home"></i>
                        <span>JelajahVilla</span>
                    </div>
                    
                    <div class="welcome-text">
                        <h2>Selamat Datang Kembali!</h2>
                        <p>Temukan pengalaman menginap yang tak terlupakan dengan vila-vila terbaik pilihan kami</p>
                    </div>
                    
                    <div class="illustration">
                        <i class="fas fa-mountain"></i>
                        <i class="fas fa-tree"></i>
                        <i class="fas fa-sun"></i>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="login-right">
                <div class="form-container">
                    <div class="form-header">
                        <h3>Masuk ke Akun Anda</h3>
                        <p>Silakan masukkan detail login Anda</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger custom-alert" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="form-login" class="login-form" novalidate>
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
                                   autofocus>
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
                                       required>
                                       <button type="button" 
                                        class="password-toggle"
                                        onclick="togglePassword('password', 'toggleIcon')"
                                        aria-label="Tampilkan/sembunyikan password">
                                    <i id="toggleIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-login">
                            <span>Masuk</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>Belum punya akun? 
                            <a href="register.php" class="register-link">Daftar di sini</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script src="../js/login.js"></script>

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


=======
>>>>>>> 40b926759f21a996741b8fae764a09c3ae54cbb2
</body>
</html>