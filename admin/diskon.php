<?php
include '../DB/koneksi.php';

// Proses simpan diskon
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_vila = intval($_POST['id_vila']);
    $persen = intval($_POST['persen']);

    if ($id_vila > 0 && $persen >= 0 && $persen <= 100) {
        $cek = mysqli_query($koneksi, "SELECT * FROM diskon WHERE id_vila = $id_vila");
        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($koneksi, "UPDATE diskon SET persen = $persen WHERE id_vila = $id_vila");
        } else {
            mysqli_query($koneksi, "INSERT INTO diskon (id_vila, persen) VALUES ($id_vila, $persen)");
        }
    }
}

$vila_result = mysqli_query($koneksi, "SELECT * FROM vila");
$diskon_result = mysqli_query($koneksi, "SELECT d.*, v.nama FROM diskon d JOIN vila v ON d.id_vila = v.id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Diskon Vila</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4 text-center">Kelola Diskon Vila</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="post">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-5">
                        <label for="id_vila">Pilih Vila:</label>
                        <select name="id_vila" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <?php while ($v = mysqli_fetch_assoc($vila_result)) : ?>
                                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nama']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="persen">Diskon (%):</label>
                        <input type="number" name="persen" class="form-control" min="0" max="100" required>
                    </div>

                    <div class="form-group col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <h4 class="mb-3">Diskon yang Aktif</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>Nama Vila</th>
                    <th>Diskon</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($d = mysqli_fetch_assoc($diskon_result)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($d['nama']) ?></td>
                        <td><span class="badge badge-success"><?= $d['persen'] ?>%</span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
