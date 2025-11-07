<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cekuser.php';
redirectIfNotAdmin();

$error = '';
$success = '';
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$upload_dir = __DIR__ . '/../assets/img/candidates/';

if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        $error = "Gagal membuat folder upload. Periksa permission folder.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama = trim($_POST['nama']);
        $kelas = trim($_POST['kelas']);
        $visi = trim($_POST['visi']);
        $misi = trim($_POST['misi']);
        $Program = trim($_POST['Program']);
        
        if (empty($nama) || empty($kelas) || empty($visi) || empty($misi)) {
            throw new Exception("Semua field harus diisi!");
        }

        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['foto']['name'];
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_size = $_FILES['foto']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_extensions)) {
                throw new Exception("Hanya file JPG, JPEG, PNG, atau GIF yang diizinkan.");
            }
            
            if ($file_size > 2097152) { // 2MB
                throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
            }
            
            $new_filename = 'candidate_' . uniqid() . '.' . $file_ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $target_path)) {
                $foto = $new_filename;
            } else {
                throw new Exception("Gagal menyimpan file. Periksa permission folder.");
            }
        } else {
            throw new Exception("File foto wajib diupload.");
        }

        $stmt = $pdo->prepare("INSERT INTO candidates (foto, nama, kelas, visi, misi, Program) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$foto, $nama, $kelas, $visi, $misi, $Program]);
        
        $success = "Calon berhasil ditambahkan!";
        $_POST = []; 
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        
        if (isset($target_path) && file_exists($target_path)) {
            unlink($target_path);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Calon - Pemilihan Ketua OSIS</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.ico">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <h1>Tambah Calon Ketua OSIS</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="candidateForm">
            <div class="form-group">
                <label for="foto">Foto Calon (Maks. 2MB, JPG/PNG)</label>
                <input type="file" id="foto" name="foto" accept="image/*" required>
                <img id="imagePreview" class="preview-image" alt="Preview Gambar">
            </div>
            
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="kelas">Kelas</label>
                <input type="text" id="kelas" name="kelas" value="<?= htmlspecialchars($_POST['kelas'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="visi">Visi</label>
                <textarea id="visi" name="visi" rows="3" required><?= htmlspecialchars($_POST['visi'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="misi">Misi</label>
                <textarea id="misi" name="misi" rows="5" required><?= htmlspecialchars($_POST['misi'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="misi">Program Unggulan</label>
                <textarea id="Program" name="Program" rows="5" required><?= htmlspecialchars($_POST['Program'] ?? '') ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Tambah Calon</button>
                <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });

        document.getElementById('candidateForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('foto');
            const maxSize = 2 * 1024 * 1024; // 2MB
            
            if (fileInput.files[0] && fileInput.files[0].size > maxSize) {
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                e.preventDefault();
            }
        });
    </script>
    
    <?php include __DIR__ . '/../includes/footeradmin.php'; ?>

        <script>
        document.querySelector('a[href*="keluar.php"]').addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>