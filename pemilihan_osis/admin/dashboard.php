<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cekuser.php';
requireAdmin();

$page_title = "Dashboard Admin";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css?v=3">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-4">
        <h1 class="mb-4 text-center"><?= $page_title ?></h1>

        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="card shadow h-100 card-hover" onclick="window.location.href='tambah_calon.php'">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-user-plus fa-2x mb-3 text-primary"></i>
                        <h5 class="card-title">Tambah Calon</h5>
                        <p class="card-text">Tambahkan calon ketua OSIS baru</p>
                    </div>
                </div>
            </div>


            <div class="col-12 col-md-4">
                <div class="card shadow h-100 card-hover" role="button" onclick="window.location.href='tambah_akun.php'">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-user-gear fa-2x mb-3 text-success"></i>
                        <h5 class="card-title">Tambah Akun</h5>
                        <p class="card-text">Buat akun untuk siswa dan guru</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card shadow h-100 card-hover" role="button" onclick="window.location.href='hasil.php'">
                    <div class="card-body text-center">
                        <i class="fa-solid fa-chart-bar fa-2x mb-3 text-danger"></i>
                        <h5 class="card-title">Hasil Pemilihan</h5>
                        <p class="card-text">Lihat hasil pemilihan secara real-time</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12 col-md-6 mx-auto">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="mb-3">Ubah Password</h4>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">Password berhasil diubah!</div>
                        <?php endif; ?>

                        <form method="POST" action="ubah_password.php">
                            <div class="mb-3">
                                <label class="form-label">Password Saat Ini</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ubah Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <?php include '../includes/footeradmin.php'; ?>

        <script>
        document.querySelector('a[href*="keluar.php"]').addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar?')) {
                e.preventDefault();
            }
        });
    </script>

</body>
</html>
