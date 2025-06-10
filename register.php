<?php
include '../DB/koneksi.php';

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi!";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username hanya boleh huruf, angka, dan underscore (_).";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $cek = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_query($koneksi, "INSERT INTO admin (username, password) VALUES ('$username', '$hashed_password')");
            if ($insert) {
                header("Location: login.php?register=success");
                exit;
            } else {
                $error = "Registrasi gagal: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Register - JelajahVilla</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="../css/register.css">
  
</head>
<body>
<div class="container-fluid">
    <div class="row no-gutters">
        <!-- Gambar di sisi kiri -->
        <div class="col-md-6 left-image d-none d-md-block"></div>

        <!-- Form di sisi kanan -->
        <div class="col-md-6 right-form">
            <div class="form-container">
                <h2 class="mb-4 text-dark font-weight-bold">Daftar Akun JelajahVilla</h2>
                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            required
                            autofocus
                            pattern="[a-zA-Z0-9_]+"
                            title="Username hanya boleh huruf, angka, dan underscore (_)."
                        />
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                required
                                minlength="6"
                                aria-describedby="passwordHelp"
                            />
                            <div class="input-group-append">
                                <span class="input-group-text" onclick="togglePassword()" role="button" aria-label="Tampilkan/sembunyikan password">
                                    <i id="toggleIcon" class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <small id="passwordHelp" class="form-text text-muted">
                            Minimal 6 karakter. Gunakan kombinasi huruf dan angka untuk keamanan yang lebih baik.
                        </small>
                    </div>

                    <button type="submit" name="register" class="btn btn-danger  btn-block">
                        Daftar
                    </button>
                </form>

                <p class="mt-3 text-center">
                    Sudah punya akun? <a href="login.php">Login di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Pindahkan fokus ke password jika tekan Enter di username
    document.getElementById('username').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Mencegah submit
            document.getElementById('password').focus(); // Pindahkan fokus ke password
        }
    });
</script>
</body>
</html>