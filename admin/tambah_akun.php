<?php
require_once '../includes/koneksi.php';
require_once '../includes/functions.php';
require_once '../includes/cekuser.php';
redirectIfNotAdmin();

$error = null;
$successMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = "Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.";
    } else {
        $action = (string) ($_POST['action'] ?? 'create');

        if ($action === 'delete') {
            $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            if (!$userId || $userId <= 0) {
                $error = "ID akun tidak valid.";
            } else {
                $targetStmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ? LIMIT 1");
                $targetStmt->execute([$userId]);
                $targetUser = $targetStmt->fetch(PDO::FETCH_ASSOC);

                if (!$targetUser || !in_array($targetUser['role'], ['siswa', 'guru'], true)) {
                    http_response_code(400);
                    $error = "Hanya akun siswa atau guru yang dapat dihapus.";
                } else {
                    $voteStmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE user_id = ?");
                    $voteStmt->execute([$userId]);
                    if ((int) $voteStmt->fetchColumn() > 0) {
                        $error = "Akun tidak dapat dihapus karena sudah memberikan suara.";
                    } else {
                        $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role IN ('siswa', 'guru')");
                        $delStmt->execute([$userId]);
                        header('Location: tambah_akun.php?deleted=1');
                        exit;
                    }
                }
            }
        } elseif ($action === 'reset_password') {
            $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $newPassword = (string) ($_POST['new_password'] ?? '');

            if (!$userId || $userId <= 0) {
                $error = "ID akun tidak valid.";
            } elseif (strlen($newPassword) < 6) {
                $error = "Password baru minimal 6 karakter.";
            } else {
                $targetStmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ? LIMIT 1");
                $targetStmt->execute([$userId]);
                $targetUser = $targetStmt->fetch(PDO::FETCH_ASSOC);

                if (!$targetUser || !in_array($targetUser['role'], ['siswa', 'guru'], true)) {
                    http_response_code(400);
                    $error = "Hanya akun siswa atau guru yang dapat direset.";
                } else {
                    $updStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role IN ('siswa', 'guru')");
                    $updStmt->execute([hashPassword($newPassword), $userId]);
                    header('Location: tambah_akun.php?reset=1');
                    exit;
                }
            }
        } else {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $role = (string) ($_POST['role'] ?? '');

            if (empty($username) || empty($password) || empty($role)) {
                $error = "Semua field harus diisi!";
            } elseif (!in_array($role, ['siswa', 'guru'], true)) {
                http_response_code(400);
                $error = "Hak akses (role) tidak valid!";
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);

                if ($stmt->fetch()) {
                    $error = "Username sudah digunakan! Silakan gunakan username lain.";
                } else {
                    $hashedPassword = hashPassword($password);

                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $hashedPassword, $role]);

                    header('Location: tambah_akun.php?success=1');
                    exit;
                }
            }
        }
    }
}

if (isset($_GET['success'])) {
    $successMessage = "Akun berhasil ditambahkan dan disimpan di database!";
} elseif (isset($_GET['reset'])) {
    $successMessage = "Password akun berhasil diperbarui!";
} elseif (isset($_GET['deleted'])) {
    $successMessage = "Akun pemilih berhasil dihapus!";
}

$accountsList = $pdo->query("
    SELECT id, username, role, sudah_vote, created_at
    FROM users
    WHERE role IN ('siswa', 'guru')
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Tambah Akun - Pemilihan Ketua OSIS";
$extra_head = <<<'HTML'
    <style>
        /* Variabel Warna Modern */
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --surface: #ffffff;
            --background: #f3f4f6;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: #d1d5db;
            --input-focus: #818cf8;
            --radius-md: 12px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        .page-container {
            max-width: 820px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
            box-shadow: 0 8px 15px rgba(79, 70, 229, 0.3);
            transform: rotate(-5deg);
            transition: transform 0.3s ease;
        }

        .page-container:hover .header-icon {
            transform: rotate(0deg) scale(1.05);
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 5px;
        }

        /* Form Card */
        .form-card {
            background: var(--surface);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-main);
        }

        /* Input dengan Ikon di dalamnya */
        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
            transition: color 0.3s ease;
            pointer-events: none;
        }

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
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            background-color: var(--surface);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        /* Styling khusus Select */
        select.form-control {
            appearance: none;
            cursor: pointer;
        }

        .select-wrapper::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        /* Tombol */
        .form-actions {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        @media (min-width: 576px) {
            .form-actions {
                flex-direction: row;
            }
            .form-actions .btn-modern {
                flex: 1;
            }
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
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-secondary-modern {
            background-color: #ffffff;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-secondary-modern:hover {
            background-color: #f3f4f6;
        }

        /* Alerts */
        .alert-modern {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeInDown 0.4s ease;
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

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 575.98px) {
            .page-container {
                margin: 20px auto;
                padding: 0 12px;
            }
            .form-card { padding: 18px 14px; }
        }
    </style>
HTML;
include '../includes/header.php';
?>
    
    <div class="page-container">
        
        <div class="page-header">
            <div class="header-icon">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h1>Kelola Akun Pengguna</h1>
            <p>Buat dan kelola akun untuk Siswa atau Guru</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-modern alert-error">
                <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            <div class="alert-modern alert-success">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" class="form-control" required autocomplete="off">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">Hak Akses (Role)</label>
                    <div class="input-wrapper select-wrapper">
                        <select id="role" name="role" class="form-control" required>
                            <option value="" disabled selected>Pilih Hak Akses</option>
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fa-solid fa-plus"></i> Tambah Akun
                    </button>
                    <a href="dashboard.php" class="btn-modern btn-secondary-modern">
                        Batal
                    </a>
                </div>
                
            </form>
        </div>

        <div class="form-card">
            <h4 class="mb-3 fw-bold"><i class="fa-solid fa-users-gear me-2 text-primary"></i>Daftar Akun Pemilih (<?= count($accountsList) ?>)</h4>
            <?php if (empty($accountsList)): ?>
                <p class="text-muted mb-0">Belum ada akun siswa atau guru.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status Voting</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accountsList as $acc): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($acc['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="badge <?= $acc['role'] === 'guru' ? 'bg-primary' : 'bg-success' ?>">
                                            <?= htmlspecialchars(strtoupper($acc['role']), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ((int) $acc['sudah_vote'] === 1): ?>
                                            <span class="badge bg-secondary">Sudah Vote</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border">Belum Vote</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex flex-wrap justify-content-end gap-2">
                                            <form method="POST" class="d-inline-flex align-items-center gap-1">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="reset_password">
                                                <input type="hidden" name="user_id" value="<?= (int) $acc['id'] ?>">
                                                <input type="password" name="new_password" class="form-control form-control-sm" style="width:120px;padding:4px 8px;" placeholder="Pass baru (6+)" minlength="6" required>
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Reset Password">
                                                    <i class="fa-solid fa-key"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Hapus akun ini?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?= (int) $acc['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" <?= (int) $acc['sudah_vote'] === 1 ? 'disabled title="Sudah memberikan suara"' : '' ?>>
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footeradmin.php'; ?>

    <script>
        document.querySelector('a[href*="keluar.php"]')?.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                e.preventDefault();
            }
        });
        
        if(window.history.replaceState) {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success') || urlParams.has('reset') || urlParams.has('deleted')) {
                setTimeout(() => {
                    urlParams.delete('success');
                    urlParams.delete('reset');
                    urlParams.delete('deleted');
                    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                    window.history.replaceState(null, '', newUrl);
                }, 3000);
            }
        }
    </script>
