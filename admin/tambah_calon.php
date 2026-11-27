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

function saveValidatedCandidatePhoto(array $file, string $uploadDir, array $allowedMimeMap, array $allowedImageTypes): string
{
    $fileTmp = $file['tmp_name'];
    $fileSize = (int) $file['size'];

    if (!is_uploaded_file($fileTmp)) {
        throw new Exception("File upload tidak valid.");
    }

    if ($fileSize <= 0 || $fileSize > 2097152) {
        throw new Exception("Ukuran file terlalu besar atau kosong. Maksimal 2MB.");
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $fileTmp) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    if ($mimeType === false || !isset($allowedMimeMap[$mimeType])) {
        throw new Exception("Hanya file gambar JPG, PNG, atau GIF yang diizinkan.");
    }

    $imageInfo = @getimagesize($fileTmp);
    if ($imageInfo === false || empty($imageInfo[0]) || empty($imageInfo[1]) || !in_array($imageInfo[2], $allowedImageTypes, true)) {
        throw new Exception("File yang diupload bukan gambar yang valid.");
    }

    $safeExt = $allowedMimeMap[$mimeType];
    $newFilename = 'candidate_' . bin2hex(random_bytes(8)) . '.' . $safeExt;
    $targetPath = $uploadDir . $newFilename;

    if (!move_uploaded_file($fileTmp, $targetPath)) {
        throw new Exception("Gagal menyimpan file. Periksa permission folder.");
    }

    return $newFilename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'save');
    $target_path = null;

    try {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            throw new Exception("Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.");
        }

        if ($action === 'delete') {
            $deleteId = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);
            if (!$deleteId || $deleteId <= 0) {
                throw new Exception("ID kandidat tidak valid.");
            }

            $candStmt = $pdo->prepare("SELECT id, foto FROM candidates WHERE id = ? LIMIT 1");
            $candStmt->execute([$deleteId]);
            $candRow = $candStmt->fetch(PDO::FETCH_ASSOC);
            if (!$candRow) {
                throw new Exception("Kandidat tidak ditemukan.");
            }

            $voteCountStmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE candidate_id = ?");
            $voteCountStmt->execute([$deleteId]);
            if ((int) $voteCountStmt->fetchColumn() > 0) {
                throw new Exception("Kandidat tidak dapat dihapus karena sudah memiliki suara.");
            }

            $delStmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
            $delStmt->execute([$deleteId]);

            if (!empty($candRow['foto']) && $candRow['foto'] !== 'default.jpg') {
                $oldFile = $upload_dir . basename($candRow['foto']);
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }

            $success = "Calon berhasil dihapus!";
        } else {
            $editId = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);
            $isEdit = $editId && $editId > 0;

            $nama = trim((string) ($_POST['nama'] ?? ''));
            $kelas = trim((string) ($_POST['kelas'] ?? ''));
            $visi = trim((string) ($_POST['visi'] ?? ''));
            $misi = trim((string) ($_POST['misi'] ?? ''));
            $Program = trim((string) ($_POST['Program'] ?? ''));

            if (empty($nama) || empty($kelas) || empty($visi) || empty($misi)) {
                throw new Exception("Semua field harus diisi!");
            }

            if ($isEdit) {
                $existingStmt = $pdo->prepare("SELECT id, foto FROM candidates WHERE id = ? LIMIT 1");
                $existingStmt->execute([$editId]);
                $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);
                if (!$existing) {
                    throw new Exception("Data calon tidak ditemukan.");
                }

                $foto = $existing['foto'];
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $newFoto = saveValidatedCandidatePhoto($_FILES['foto'], $upload_dir, $allowed_mime_map, $allowed_image_types);
                    $target_path = $upload_dir . $newFoto;
                    $foto = $newFoto;
                }

                $stmt = $pdo->prepare("UPDATE candidates SET foto = ?, nama = ?, kelas = ?, visi = ?, misi = ?, Program = ? WHERE id = ?");
                $stmt->execute([$foto, $nama, $kelas, $visi, $misi, $Program, $editId]);

                if ($target_path !== null && !empty($existing['foto']) && $existing['foto'] !== 'default.jpg') {
                    $oldFile = $upload_dir . basename($existing['foto']);
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }

                header('Location: tambah_calon.php?updated=1');
                exit;
            } else {
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $foto = saveValidatedCandidatePhoto($_FILES['foto'], $upload_dir, $allowed_mime_map, $allowed_image_types);
                    $target_path = $upload_dir . $foto;
                } else {
                    throw new Exception("File foto wajib diupload.");
                }

                $stmt = $pdo->prepare("INSERT INTO candidates (foto, nama, kelas, visi, misi, Program) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$foto, $nama, $kelas, $visi, $misi, $Program]);

                $success = "Calon berhasil ditambahkan!";
                $_POST = [];
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();

        if ($target_path !== null && file_exists($target_path)) {
            @unlink($target_path);
        }
    }
}

if (isset($_GET['updated'])) {
    $success = "Data calon berhasil diperbarui!";
}

$editingCandidate = null;
if (isset($_GET['edit'])) {
    $editIdParam = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editIdParam && $editIdParam > 0) {
        $editStmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ? LIMIT 1");
        $editStmt->execute([$editIdParam]);
        $editingCandidate = $editStmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

$candidatesList = $pdo->query("
    SELECT c.*, COUNT(v.id) AS total_suara
    FROM candidates c
    LEFT JOIN votes v ON c.id = v.candidate_id
    GROUP BY c.id
    ORDER BY c.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$formValues = [
    'nama' => $_POST['nama'] ?? ($editingCandidate['nama'] ?? ''),
    'kelas' => $_POST['kelas'] ?? ($editingCandidate['kelas'] ?? ''),
    'visi' => $_POST['visi'] ?? ($editingCandidate['visi'] ?? ''),
    'misi' => $_POST['misi'] ?? ($editingCandidate['misi'] ?? ''),
    'Program' => $_POST['Program'] ?? ($editingCandidate['Program'] ?? ''),
];

$page_title = ($editingCandidate ? "Edit Calon" : "Tambah Calon") . " - Pemilihan Ketua OSIS";
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
            max-width: 960px;
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
            margin-bottom: 30px;
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

        .candidate-thumb {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
HTML;
include __DIR__ . '/../includes/header.php';
?>
    
    <div class="page-container">
        
        <div class="page-header">
            <i class="fa-solid <?= $editingCandidate ? 'fa-user-pen' : 'fa-user-plus' ?>"></i>
            <h1><?= $editingCandidate ? 'Edit Calon Ketua OSIS' : 'Tambah Calon Ketua OSIS' ?></h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-modern alert-error">
                <i class="fa-solid fa-circle-exclamation fs-5"></i>
                <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-modern alert-success">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST" enctype="multipart/form-data" id="candidateForm">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save">
                <?php if ($editingCandidate): ?>
                    <input type="hidden" name="candidate_id" value="<?= (int) $editingCandidate['id'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    
                    <div class="form-group full-width">
                        <label>Foto Calon (Maks. 2MB, JPG/PNG<?= $editingCandidate ? ' — opsional saat edit' : '' ?>)</label>
                        <div class="upload-area">
                            <input type="file" id="foto" name="foto" accept="image/*" <?= $editingCandidate ? '' : 'required' ?>>
                            <div class="upload-placeholder" id="uploadPlaceholder" <?= $editingCandidate ? 'style="display:none;"' : '' ?>>
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Klik atau seret foto ke area ini</span>
                            </div>
                            <img id="imagePreview" class="preview-image" alt="Preview Gambar"
                                 <?php if ($editingCandidate): ?>
                                     src="<?= BASE_URL ?>/assets/img/candidates/<?= htmlspecialchars($editingCandidate['foto'], ENT_QUOTES, 'UTF-8') ?>"
                                     style="display:inline-block;"
                                     onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/img/default.jpg'"
                                 <?php endif; ?>>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" value="<?= htmlspecialchars($formValues['nama'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="kelas">Kelas</label>
                        <input type="text" id="kelas" name="kelas" class="form-control" placeholder="Contoh: XI RPL 1" value="<?= htmlspecialchars($formValues['kelas'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="visi">Visi</label>
                        <textarea id="visi" name="visi" class="form-control" placeholder="Tuliskan visi calon di sini..." required><?= htmlspecialchars($formValues['visi'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="misi">Misi</label>
                        <textarea id="misi" name="misi" class="form-control" placeholder="Tuliskan misi calon (bisa menggunakan nomor urut)..." required><?= htmlspecialchars($formValues['misi'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="Program">Program Unggulan</label>
                        <textarea id="Program" name="Program" class="form-control" placeholder="Tuliskan program unggulan calon..." required><?= htmlspecialchars($formValues['Program'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fa-solid fa-save"></i> <?= $editingCandidate ? 'Simpan Perubahan' : 'Simpan Data Calon' ?>
                    </button>
                    <?php if ($editingCandidate): ?>
                        <a href="tambah_calon.php" class="btn-modern btn-secondary-modern">
                            Batal Edit
                        </a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn-modern btn-secondary-modern">
                            Batal & Kembali
                        </a>
                    <?php endif; ?>
                </div>

            </form>
        </div>

        <div class="form-card">
            <h4 class="mb-3 fw-bold"><i class="fa-solid fa-users me-2 text-primary"></i>Daftar Kandidat Terdaftar (<?= count($candidatesList) ?>)</h4>
            <?php if (empty($candidatesList)): ?>
                <p class="text-muted mb-0">Belum ada kandidat terdaftar.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Suara</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidatesList as $c): ?>
                                <tr>
                                    <td>
                                        <img src="<?= BASE_URL ?>/assets/img/candidates/<?= htmlspecialchars($c['foto'], ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($c['nama'], ENT_QUOTES, 'UTF-8') ?>"
                                             class="candidate-thumb"
                                             onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/img/default.jpg'">
                                    </td>
                                    <td class="fw-semibold"><?= htmlspecialchars($c['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($c['kelas'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= (int) $c['total_suara'] ?></span></td>
                                    <td class="text-end">
                                        <a href="tambah_calon.php?edit=<?= (int) $c['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus kandidat ini?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="candidate_id" value="<?= (int) $c['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" <?= (int) $c['total_suara'] > 0 ? 'disabled title="Sudah memiliki suara"' : '' ?>>
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('uploadPlaceholder');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'inline-block';
                    placeholder.style.display = 'none';
                }
                
                reader.readAsDataURL(file);
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
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
