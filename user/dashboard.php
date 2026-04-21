<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cekuser.php';

$sudah_vote = false;
$bisa_vote = false;

if ($_SESSION['role'] === 'siswa' || $_SESSION['role'] === 'guru') {
    $stmt = $pdo->prepare("SELECT COUNT(*) as sudah_vote FROM votes WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    
    $sudah_vote = ($result['sudah_vote'] > 0);
    $bisa_vote = !$sudah_vote;
    
    if ($_SESSION['role'] === 'siswa') {
        $stmt = $pdo->prepare("SELECT sudah_vote FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $sudah_vote = $sudah_vote || $user['sudah_vote'];
        $bisa_vote = !$sudah_vote;
    }
}

$total_user = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('siswa','guru')")->fetchColumn();
$total_vote = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
$total_candidate = $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
$progress = $total_user > 0 ? ($total_vote / $total_user) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - <?= htmlspecialchars($_SESSION['role']) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.ico">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* CSS Variables untuk Tema Modern */
        :root {
            --primary: #4f46e5;      /* Indigo modern */
            --primary-hover: #4338ca;
            --surface: #ffffff;
            --background: #f3f4f6;   /* Soft gray */
            --text-main: #111827;
            --text-muted: #6b7280;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* Mobile First Container */
        .dashboard-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 16px; /* Padding lebih bersahabat untuk HP */
        }

        /* User Info Card */
        .user-info {
            background: var(--surface);
            padding: 24px 20px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .user-info h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .user-info p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Modern Badges (Pill shape) */
        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .siswa-badge { background-color: #dcfce7; color: #166534; }
        .guru-badge { background-color: #dbeafe; color: #1e40af; }
        .admin-badge { background-color: #fee2e2; color: #991b1b; }

        /* Stats Grid - Mobile First (1 column) */
        .dashboard-menu {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            margin-top: 24px;
        }

        .menu-card {
            background: var(--surface);
            padding: 20px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .menu-card h2 {
            font-size: 0.95rem;
            color: var(--text-muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .menu-card h2 i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        .menu-card p {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
        }

        /* Modern Progress Bar */
        .progress-container {
            background: var(--surface);
            padding: 20px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            margin: 24px 0;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .progress-container p {
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .progress-bar {
            width: 100%;
            height: 12px;
            background: #e5e7eb;
            border-radius: 9999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4f46e5, #ec4899); /* Modern gradient */
            border-radius: 9999px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Modern Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            font-size: 0.95rem;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .alert-info {
            background-color: #eff6ff;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        /* Action Buttons - Mobile First (Full width) */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100%; /* Full width di HP */
            padding: 14px 24px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: background-color 0.2s, transform 0.1s;
            box-shadow: var(--shadow-sm);
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: var(--text-main);
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background-color: #f9fafb;
        }

        .voting-info {
            text-align: center;
            margin-top: 32px;
            color: var(--text-muted);
            font-size: 0.9rem;
            padding: 0 16px;
        }

        /* --- Media Queries untuk Tablet & Desktop --- */
        @media (min-width: 640px) {
            .dashboard-menu {
                grid-template-columns: repeat(2, 1fr);
            }
            .user-info h1 {
                font-size: 1.5rem;
            }
        }

        @media (min-width: 768px) {
            .dashboard-container {
                padding: 32px 24px;
            }
            .dashboard-menu {
                grid-template-columns: repeat(3, 1fr);
            }
            .action-buttons {
                flex-direction: row; /* Berjejer ke samping di Desktop */
                justify-content: flex-start;
            }
            .btn {
                width: auto; /* Ukuran menyesuaikan teks di Desktop */
            }
            .alert {
                flex-direction: row;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <div class="user-info">
            <h1>
                Selamat datang, 
                <?php if ($_SESSION['role'] === 'guru'): ?>
                    Bapak/Ibu
                <?php elseif ($_SESSION['role'] === 'siswa'): ?>
                    Akang/Teteh
                <?php else: ?>
                    Admin
                <?php endif; ?>
                <span class="role-badge <?= $_SESSION['role'] ?>-badge">
                    <?= strtoupper($_SESSION['role']) ?>
                </span>
            </h1>
            
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <p>Anda masuk sebagai administrator sistem pemilihan. Kelola jalannya pemilihan melalui panel ini.</p>
            <?php elseif ($_SESSION['role'] === 'guru'): ?>
                <p>Anda masuk sebagai guru yang berhak memberikan suara dalam pemilihan ketua OSIS tahun ini.</p>
            <?php else: ?>
                <p>Anda masuk sebagai siswa yang berhak memilih ketua OSIS. Gunakan hak suara kamu dengan bijak!</p>
            <?php endif; ?>
        </div>

        <div class="alert alert-info">
            <i class="fa-solid fa-bullhorn" style="font-size: 1.2rem; margin-right: 8px;"></i>
            <div>
                <strong>Info Penting:</strong> Pemilihan akan ditutup pada <b>25 Oktober 2025</b>. Pastikan kamu sudah memberikan suara!
            </div>
        </div>

        <?php if ($sudah_vote): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check" style="font-size: 1.5rem; margin-right: 12px;"></i>
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 1rem;">Terima Kasih!</h3>
                    <p style="margin: 0; font-size: 0.9rem;">Anda sudah memberikan suara. Hasil pemilihan akan diumumkan setelah periode voting selesai.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="dashboard-menu">
            <div class="menu-card">
                <h2><i class="fa-solid fa-users"></i> Total Kandidat</h2>
                <p><?= $total_candidate ?> <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-muted);">orang</span></p>
            </div>
            <div class="menu-card">
                <h2><i class="fa-solid fa-check-to-slot"></i> Sudah Memilih</h2>
                <p><?= $total_vote ?> <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-muted);">dari <?= $total_user ?></span></p>
            </div>
            <div class="menu-card">
                <h2><i class="fa-solid fa-calendar-days"></i> Jadwal</h2>
                <p style="font-size: 1.1rem;">24 – 25 Okt 2025</p>
            </div>
        </div>

        <div class="progress-container">
            <p><i class="fa-solid fa-chart-line" style="color: var(--primary); margin-right: 8px;"></i> Partisipasi Pemilih: <?= round($progress,1) ?>%</p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
            </div>
        </div>

        <div class="action-buttons">
            <?php if ($bisa_vote): ?>
                <?php if ($_SESSION['role'] === 'siswa'): ?>
                    <a href="<?= BASE_URL ?>/user/vote.php" class="btn"><i class="fa-solid fa-check-to-slot" style="margin-right: 8px;"></i> Pilih Calon Ketua OSIS</a>
                <?php elseif ($_SESSION['role'] === 'guru'): ?>
                    <a href="<?= BASE_URL ?>/user/vote_guru.php" class="btn"><i class="fa-solid fa-check-to-slot" style="margin-right: 8px;"></i> Berikan Suara Guru</a>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn"><i class="fa-solid fa-gauge-high" style="margin-right: 8px;"></i> Ke Admin Dashboard</a>
            <?php endif; ?>
            
            <a href="<?= BASE_URL ?>/keluar.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-right-from-bracket" style="margin-right: 8px;"></i> Keluar</a>
        </div>

        <?php if ($_SESSION['role'] !== 'admin' && !$sudah_vote): ?>
            <div class="voting-info">
                <strong>Informasi Pemilihan</strong>
                <p style="margin-top: 8px;">Silakan pilih salah satu calon dengan menekan tombol di atas. Anda hanya dapat memilih satu kali.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
        document.querySelector('a[href*="keluar.php"]').addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                e.preventDefault();
            }
        });
        
        // Animasi Progress Bar saat diload
        document.addEventListener("DOMContentLoaded", function() {
            const progressFill = document.querySelector('.progress-fill');
            const targetWidth = progressFill.style.width;
            progressFill.style.width = '0%';
            setTimeout(() => {
                progressFill.style.width = targetWidth;
            }, 100);
        });
    </script>
</body>
</html>