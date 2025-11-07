<?php
require_once '../includes/cekuser.php';
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (empty($username) || empty($password) || empty($role)) {
        $error = "Semua field harus diisi!";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = "Username sudah digunakan!";
        } else {
            $hashedPassword = hashPassword($password);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $role]);
            
            header('Location: dashboard.php?success=1');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun - Pemilihan Ketua OSIS</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1>Tambah Akun Baru</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Akun berhasil ditambahkan!</div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">Pilih Role</option>
                    <option value="siswa">Siswa</option>
                    <option value="guru">Guru</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Tambah Akun</button>
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </form>
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