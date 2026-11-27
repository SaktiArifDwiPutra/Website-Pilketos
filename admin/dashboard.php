<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cekuser.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_settings') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        header('Location: dashboard.php?settings_error=Token+keamanan+tidak+valid');
        exit;
    }

    $electionTitle = trim((string) ($_POST['election_title'] ?? ''));
    $votingStartRaw = trim((string) ($_POST['voting_start'] ?? ''));
    $votingEndRaw = trim((string) ($_POST['voting_end'] ?? ''));
    $isVotingOpen = isset($_POST['is_voting_open']) && $_POST['is_voting_open'] === '1' ? 1 : 0;

    if ($electionTitle === '') {
        header('Location: dashboard.php?settings_error=Judul+pemilihan+tidak+boleh+kosong');
        exit;
    }

    $votingStart = $votingStartRaw !== '' ? date('Y-m-d H:i:s', strtotime($votingStartRaw)) : null;
    $votingEnd = $votingEndRaw !== '' ? date('Y-m-d H:i:s', strtotime($votingEndRaw)) : null;

    if ($votingStart !== null && $votingEnd !== null && strtotime($votingEnd) < strtotime($votingStart)) {
        header('Location: dashboard.php?settings_error=Waktu+selesai+tidak+boleh+lebih+awal+dari+waktu+mulai');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE election_settings SET election_title = ?, voting_start = ?, voting_end = ?, is_voting_open = ? WHERE id = 1");
    $stmt->execute([$electionTitle, $votingStart, $votingEnd, $isVotingOpen]);

    header('Location: dashboard.php?settings_saved=1');
    exit;
}

$page_title = "Dashboard Admin";

// Ambil statistik sederhana untuk ditampilkan di dashboard (Opsional tapi keren)
$total_kandidat = $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
$total_pemilih = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('siswa', 'guru')")->fetchColumn();
$suara_masuk = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
$electionSettings = getElectionStatus($pdo);

$extra_head = <<<'HTML'
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --surface: #ffffff;
            --background: #f3f4f6;
            --text-main: #111827;
            --text-muted: #6b7280;
            --radius-lg: 16px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
        }

        /* Header Title Area */
        .dashboard-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 40px 20px 60px;
            border-radius: 0 0 30px 30px;
            margin-bottom: -40px;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.3);
        }

        .dashboard-header h1 {
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .dashboard-header p {
            opacity: 0.9;
            font-weight: 400;
        }

        /* Menu Card Styling Modern */
        .menu-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            border: none;
            padding: 25px 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 4px;
            background: var(--primary);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
            z-index: 2;
        }

        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .menu-card:hover::before {
            transform: scaleX(1);
        }

        .icon-box {
            width: 65px;
            height: 65px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            transition: all 0.3s ease;
        }

        .menu-card:hover .icon-box {
            transform: scale(1.1) rotate(5deg);
        }

        /* Warna Ikon Spesifik */
        .bg-icon-primary { background: #e0e7ff; color: #4f46e5; }
        .bg-icon-success { background: #dcfce7; color: #16a34a; }
        .bg-icon-danger { background: #fee2e2; color: #dc2626; }

        .menu-card .card-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-main);
        }

        .menu-card .card-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        /* Statistik Badge Kecil di Kartu */
        .stat-badge {
            display: inline-block;
            background: #f3f4f6;
            color: var(--text-main);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Form Card Styling */
        .password-card {
            border-radius: var(--radius-lg);
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            padding: 10px;
        }

        .password-card .card-body {
            padding: 30px;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            transition: all 0.2s;
        }

        .form-control:focus {
            background-color: white;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-update {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(79, 70, 229, 0.3);
        }

        /* Alerts Custom */
        .alert-custom {
            border-radius: 8px;
            border: none;
            padding: 12px 16px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
HTML;
include '../includes/header.php';
?>
    
    <div class="dashboard-header text-center">
        <div class="container">
            <h1><i class="fa-solid fa-shield-halved me-2"></i>Panel Administrator</h1>
            <p>Kelola sistem pemilihan, kandidat, dan pantau perolehan suara.</p>
        </div>
    </div>

    <div class="container" style="position: relative; z-index: 10;">
        <div class="row g-4 mb-5">
            
            <div class="col-12 col-md-4">
                <a href="tambah_calon.php" class="menu-card h-100 text-center d-block text-decoration-none">
                    <div class="icon-box bg-icon-primary">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <h5 class="card-title">Kandidat OSIS</h5>
                    <p class="card-text">Kelola data calon ketua dan wakil ketua OSIS.</p>
                    <span class="stat-badge"><i class="fa-solid fa-users me-1"></i> <?= $total_kandidat ?> Terdaftar</span>
                </a>
            </div>

            <div class="col-12 col-md-4">
                <a href="tambah_akun.php" class="menu-card h-100 text-center d-block text-decoration-none">
                    <div class="icon-box bg-icon-success">
                        <i class="fa-solid fa-id-card-clip"></i>
                    </div>
                    <h5 class="card-title">Akun Pemilih</h5>
                    <p class="card-text">Buat dan kelola akun akses untuk siswa/guru.</p>
                    <span class="stat-badge"><i class="fa-solid fa-key me-1"></i> <?= $total_pemilih ?> Akun</span>
                </a>
            </div>

            <div class="col-12 col-md-4">
                <a href="hasil.php" class="menu-card h-100 text-center d-block text-decoration-none">
                    <div class="icon-box bg-icon-danger">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h5 class="card-title">Hasil Live</h5>
                    <p class="card-text">Pantau perolehan suara secara real-time.</p>
                    <span class="stat-badge"><i class="fa-solid fa-check-to-slot me-1"></i> <?= $suara_masuk ?> Suara Masuk</span>
                </a>
            </div>

        </div>

        <div class="row g-4 mb-5">
            <div class="col-12 col-lg-6">
                <div class="card password-card h-100">
                    <div class="card-body">
                        <h4 class="mb-1" style="font-weight: 700; color: var(--text-main);">
                            <i class="fa-solid fa-calendar-check text-muted me-2"></i>Pengaturan Pemilihan
                        </h4>
                        <p class="text-muted mb-4" style="font-size: 0.9rem;">Atur jadwal dan status buka/tutup pemungutan suara.</p>

                        <?php if (isset($_GET['settings_error'])): ?>
                            <div class="alert alert-danger alert-custom">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <?= htmlspecialchars($_GET['settings_error'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['settings_saved'])): ?>
                            <div class="alert alert-success alert-custom">
                                <i class="fa-solid fa-circle-check"></i>
                                Pengaturan pemilihan berhasil disimpan!
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="dashboard.php">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="update_settings">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="election_title">Judul Pemilihan</label>
                                <input type="text" id="election_title" name="election_title" class="form-control" value="<?= htmlspecialchars($electionSettings['election_title'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label fw-semibold" for="voting_start">Waktu Mulai (Opsional)</label>
                                    <input type="datetime-local" id="voting_start" name="voting_start" class="form-control" value="<?= $electionSettings['voting_start'] ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($electionSettings['voting_start'])), ENT_QUOTES, 'UTF-8') : '' ?>">
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label fw-semibold" for="voting_end">Waktu Selesai (Opsional)</label>
                                    <input type="datetime-local" id="voting_end" name="voting_end" class="form-control" value="<?= $electionSettings['voting_end'] ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($electionSettings['voting_end'])), ENT_QUOTES, 'UTF-8') : '' ?>">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold" for="is_voting_open">Status Voting</label>
                                <select id="is_voting_open" name="is_voting_open" class="form-select">
                                    <option value="1" <?= $electionSettings['is_voting_open'] === 1 ? 'selected' : '' ?>>Dibuka (Aktif)</option>
                                    <option value="0" <?= $electionSettings['is_voting_open'] === 0 ? 'selected' : '' ?>>Ditutup</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-update w-100">
                                Simpan Pengaturan Pemilihan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card password-card h-100">
                    <div class="card-body">
                        <h4 class="mb-1" style="font-weight: 700; color: var(--text-main);">
                            <i class="fa-solid fa-lock text-muted me-2"></i>Keamanan Akun
                        </h4>
                        <p class="text-muted mb-4" style="font-size: 0.9rem;">Perbarui password administrator secara berkala.</p>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-custom">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-custom">
                                <i class="fa-solid fa-circle-check"></i>
                                Password berhasil diperbarui!
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="ubah_password.php">
                            <?= csrfField() ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password Saat Ini</label>
                                <input type="password" name="current_password" class="form-control" placeholder="Masukkan password lama" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Ketik ulang password baru" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-update w-100">
                                Simpan Perubahan Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <?php include '../includes/footeradmin.php'; ?>

    <script>
        document.querySelector('a[href*="keluar.php"]')?.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                e.preventDefault();
            }
        });
        
        // Membersihkan URL dari parameter ?success/error setelah 3 detik
        if(window.history.replaceState) {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success') || urlParams.has('error') || urlParams.has('settings_saved') || urlParams.has('settings_error')) {
                setTimeout(() => {
                    urlParams.delete('success');
                    urlParams.delete('error');
                    urlParams.delete('settings_saved');
                    urlParams.delete('settings_error');
                    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                    window.history.replaceState(null, '', newUrl);
                }, 3000);
            }
        }
    </script>