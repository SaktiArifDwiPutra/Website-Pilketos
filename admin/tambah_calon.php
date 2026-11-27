<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cekuser.php';
redirectIfNotAdmin();

$error = '';
$success = '';
$allowed_mime_map = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
];
$allowed_image_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
$upload_dir = __DIR__ . '/../assets/img/candidates/';

if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        $error = "Gagal membuat folder upload. Periksa permission folder.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_path = null;
    try {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            throw new Exception("Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.");
        }

        $nama = trim((string) ($_POST['nama'] ?? ''));
        $kelas = trim((string) ($_POST['kelas'] ?? ''));
        $visi = trim((string) ($_POST['visi'] ?? ''));
        $misi = trim((string) ($_POST['misi'] ?? ''));
        $Program = trim((string) ($_POST['Program'] ?? ''));

        if (empty($nama) || empty($kelas) || empty($visi) || empty($misi)) {
            throw new Exception("Semua field harus diisi!");
        }

        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_size = (int) $_FILES['foto']['size'];

            if (!is_uploaded_file($file_tmp)) {
                throw new Exception("File upload tidak valid.");
            }

            if ($file_size <= 0 || $file_size > 2097152) { // 2MB
                throw new Exception("Ukuran file terlalu besar atau kosong. Maksimal 2MB.");
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = $finfo ? finfo_file($finfo, $file_tmp) : false;
            if ($finfo) {
                finfo_close($finfo);
            }

            if ($mime_type === false || !isset($allowed_mime_map[$mime_type])) {
                throw new Exception("Hanya file gambar JPG, PNG, atau GIF yang diizinkan.");
            }

            $image_info = @getimagesize($file_tmp);
            if ($image_info === false || empty($image_info[0]) || empty($image_info[1]) || !in_array($image_info[2], $allowed_image_types, true)) {
                throw new Exception("File yang diupload bukan gambar yang valid.");
            }

            $safe_ext = $allowed_mime_map[$mime_type];
            $new_filename = 'candidate_' . bin2hex(random_bytes(8)) . '.' . $safe_ext;
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

        if ($target_path !== null && file_exists($target_path)) {
            unlink($target_path);
        }
    }
}

$page_title = "Tambah Calon - Pemilihan Ketua OSIS";
$extra_head = <<<'HTML'
    <style>
        /* Variabel Warna Modern (Senada dengan Dashboard) */
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --surface: #ffffff;
            --background: #f3f4f6;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --input-focus: #c7d2fe;
            --radius-md: 12px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        .page-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }

        .page-header i {
            font-size: 1.5rem;
            color: var(--primary);
            background: #e0e7ff;
            padding: 10px;
            border-radius: 10px;
        }

        /* Form Card Styling */
        .form-card {
            background: var(--surface);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0,0,0,0.05);
        }

        /* Grid Layout untuk Form */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        @media (min-width: 768px) {
            .form-grid { grid-template-columns: 1fr 1fr; }
            .full-width { grid-column: 1 / -1; }
        }

        .form-group { margin-bottom: 0; }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-main);
        }

        /* Styling Input Field */
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #f9fafb;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background-color: var(--surface);
            box-shadow: 0 0 0 4px var(--input-focus);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        /* Styling Area Upload Foto */
        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
            text-align: center;
            background-color: #f9fafb;
            transition: border-color 0.3s;
            position: relative;
        }

        .upload-area:hover {
            border-color: var(--primary);
        }

        .upload-area input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .upload-placeholder i {
            font-size: 2rem;
            color: var(--text-muted);
            margin-bottom: 10px;
        }
        
        .upload-placeholder span {
            display: block;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .preview-image {
            max-width: 100%;
            max-height: 250px;
            margin-top: 15px;
            display: none;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            object-fit: cover;
        }

        /* Buttons Styling */
        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }

        .btn-modern {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            justify-content: center;
        }

        .btn-primary-modern {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
        }

        .btn-primary-modern:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-secondary-modern {
            background-color: #ffffff;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-secondary-modern:hover {
            background-color: #f3f4f6;
        }

        @media (max-width: 576px) {
            .form-actions { flex-direction: column; }
            .btn-modern { width: 100%; }
        }

        /* Alerts Styling */
        .alert-modern {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border-left: 4px solid #22c55e;
        }
    </style>
HTML;
include __DIR__ . '/../includes/header.php';
?>
    
    <div class="page-container">
        
        <div class="page-header">
            <i class="fa-solid fa-user-plus"></i>
            <h1>Tambah Calon Ketua OSIS</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-modern alert-error">
                <i class="fa-solid fa-circle-exclamation fs-5"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-modern alert-success">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div><?= htmlspecialchars($success) ?></div>
            </div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST" enctype="multipart/form-data" id="candidateForm">
                <?= csrfField() ?>
                
                <div class="form-grid">
                    
                    <div class="form-group full-width">
                        <label>Foto Calon (Maks. 2MB, JPG/PNG)</label>
                        <div class="upload-area">
                            <input type="file" id="foto" name="foto" accept="image/*" required>
                            <div class="upload-placeholder" id="uploadPlaceholder">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Klik atau seret foto ke area ini</span>
                            </div>
                            <img id="imagePreview" class="preview-image" alt="Preview Gambar">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="kelas">Kelas</label>
                        <input type="text" id="kelas" name="kelas" class="form-control" placeholder="Contoh: XI RPL 1" value="<?= htmlspecialchars($_POST['kelas'] ?? '') ?>" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="visi">Visi</label>
                        <textarea id="visi" name="visi" class="form-control" placeholder="Tuliskan visi calon di sini..." required><?= htmlspecialchars($_POST['visi'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="misi">Misi</label>
                        <textarea id="misi" name="misi" class="form-control" placeholder="Tuliskan misi calon (bisa menggunakan nomor urut)..." required><?= htmlspecialchars($_POST['misi'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="Program">Program Unggulan</label>
                        <textarea id="Program" name="Program" class="form-control" placeholder="Tuliskan program unggulan calon..." required><?= htmlspecialchars($_POST['Program'] ?? '') ?></textarea>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fa-solid fa-save"></i> Simpan Data Calon
                    </button>
                    <a href="dashboard.php" class="btn-modern btn-secondary-modern">
                        Batal & Kembali
                    </a>
                </div>

            </form>
        </div>

    </div>

    <script>
        // Script Preview Image yang diperbarui
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('uploadPlaceholder');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'inline-block';
                    placeholder.style.display = 'none'; // Sembunyikan ikon awan
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                placeholder.style.display = 'block';
            }
        });

        // Script Validasi Ukuran File
        document.getElementById('candidateForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('foto');
            const maxSize = 2 * 1024 * 1024; // 2MB
            
            if (fileInput.files[0] && fileInput.files[0].size > maxSize) {
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                e.preventDefault();
            }
        });
    </script>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>