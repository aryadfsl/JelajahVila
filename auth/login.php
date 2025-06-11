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
    <title>Login - JelajahVilla</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
          <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row no-gutters">
            <div class="col-md-6 left-image d-none d-md-block"></div>

            <div class="col-md-6 right-form">
                <div class="form-container">
                    <h2 class="mb-4 text-dark font-weight-bold text-center">
                        Login JelajahVilla
                    </h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="form-login" novalidate>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input  type="text"
                                    id="username"
                                    name="username"
                                    class="form-control"
                                    required
                                    autofocus>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <input  type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        required>
                                <div class="input-group-append">
                                    <span class="input-group-text"
                                          onclick="togglePassword()"
                                          role="button"
                                          aria-label="Tampilkan/sembunyikan password">
                                        <i id="toggleIcon" class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                                class="btn btn-danger btn-block">
                            Login
                        </button>
                    </form>

                    <p class="mt-3 text-center">
                        Belum punya akun?
                        <a href="register.php">Daftar di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('toggleIcon');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Pindah fokus ke password jika tekan Enter di username
        document.getElementById('username').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Mencegah submit
                document.getElementById('password').focus(); // Pindah ke password
            }
        });
    </script>
</body>
</html>