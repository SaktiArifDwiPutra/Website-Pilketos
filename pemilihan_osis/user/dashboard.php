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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($_SESSION['role']) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.ico">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .dashboard-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .user-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .role-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: bold;
            margin-left: 10px;
        }
        .siswa-badge { background-color: #28a745; color: white; }
        .guru-badge { background-color: #17a2b8; color: white; }
        .admin-badge { background-color: #dc3545; color: white; }

        .dashboard-menu {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .menu-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .menu-card:hover {
            transform: translateY(-5px);
        }
        .menu-card h2 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .menu-card p {
            font-size: 1rem;
            color: #555;
        }
        .progress-container {
            margin: 30px 0;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #eee;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            transition: width 0.4s ease;
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
                <p>Anda masuk sebagai administrator sistem pemilihan.</p>
            <?php elseif ($_SESSION['role'] === 'guru'): ?>
                <p>Anda masuk sebagai guru yang berhak memberikan suara dalam pemilihan ketua OSIS.</p>
            <?php else: ?>
                <p>Anda masuk sebagai siswa yang berhak memilih ketua OSIS.</p>
            <?php endif; ?>
        </div>

        <div class="dashboard-menu">
            <div class="menu-card">
                <h2><i class="fa-solid fa-users"></i> Total Kandidat</h2>
                <p><?= $total_candidate ?> orang</p>
            </div>
            <div class="menu-card">
                <h2><i class="fa-solid fa-check-to-slot"></i> Sudah Memilih</h2>
                <p><?= $total_vote ?> dari <?= $total_user ?> pemilih</p>
            </div>
            <div class="menu-card">
                <h2><i class="fa-solid fa-calendar-days"></i> Jadwal Pemilihan</h2>
                <p>24 – 25 Oktober 2025</p>
            </div>
        </div>

        <div class="progress-container">
            <p><i class="fa-solid fa-chart-line"></i> Progress Pemilihan: <?= round($progress,1) ?>%</p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="fa-solid fa-bullhorn"></i>
            <strong>Info Penting:</strong> Pemilihan akan ditutup pada <b>25 Oktober 2025</b>. 
            Pastikan kamu sudah memberikan suara ya!
        </div>

        <?php if ($sudah_vote): ?>
            <div class="alert alert-success">
                <h3>Status Voting</h3>
                <p>Anda sudah memberikan suara dalam pemilihan ketua OSIS. Terima kasih telah berpartisipasi!</p>
                <p>Hasil pemilihan akan diumumkan setelah periode voting selesai.</p>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <?php if ($bisa_vote): ?>
                <?php if ($_SESSION['role'] === 'siswa'): ?>
                    <a href="<?= BASE_URL ?>/user/vote.php" class="btn">Pilih Calon Ketua OSIS</a>
                <?php elseif ($_SESSION['role'] === 'guru'): ?>
                    <a href="<?= BASE_URL ?>/user/vote_guru.php" class="btn">Berikan Suara Guru</a>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn">Admin Dashboard</a>
            <?php endif; ?>
            
            <a href="<?= BASE_URL ?>/keluar.php" class="btn btn-secondary">Keluar</a>
        </div>

        <?php if ($_SESSION['role'] !== 'admin' && !$sudah_vote): ?>
            <div class="voting-info">
                <h3 style="padding-top: 30px;">Informasi Pemilihan</h3>
                <p>Silakan pilih salah satu calon ketua OSIS dengan menekan tombol di atas.</p>
                <p>Anda hanya dapat memilih satu kali selama periode pemilihan berlangsung.</p>
            </div><br>
        <?php endif; ?>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
        document.querySelector('a[href*="keluar.php"]').addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar?')) {
                e.preventDefault();
            }
        });
    </script>

</body>
</html>
