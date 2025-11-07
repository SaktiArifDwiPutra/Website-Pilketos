<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cekuser.php';

if ($_SESSION['role'] !== 'guru') {
    header('Location: ' . BASE_URL . '/user/dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$already_voted = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id']) && !$already_voted) {
    $candidate_id = (int)$_POST['candidate_id'];
    
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $candidate_id]);
        
        $pdo->commit();
        header('Location: ' . BASE_URL . '/user/dashboard.php?status=success');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Gagal menyimpan pilihan: " . $e->getMessage();
    }
}

$stmt = $pdo->query("SELECT * FROM candidates");
$candidates = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemilihan Ketua OSIS - Guru</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.ico">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container py-4">
        <h1 class="mb-3">Pemilihan Ketua OSIS</h1>
        <p class="text-muted">Sebagai Guru, Anda berhak memberikan suara untuk memilih ketua OSIS.</p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($already_voted): ?>
            <div class="alert alert-info">
                <strong>Anda sudah memberikan suara.</strong> Terima kasih atas partisipasinya.
                <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-sm btn-primary ms-2">Kembali ke Dashboard</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($candidates as $candidate): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm card-hover">
                        <div class="candidate-photo">
                            <img src="<?= BASE_URL ?>/assets/img/candidates/<?= htmlspecialchars($candidate['foto']) ?>" 
                                 class="card-img-top"
                                 alt="<?= htmlspecialchars($candidate['nama']) ?>"
                                 onerror="this.src='<?= BASE_URL ?>/assets/img/default.jpg'">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h3 class="card-title"><?= htmlspecialchars($candidate['nama']) ?></h3>
                            <p class="text-muted mb-3">Kelas: <?= htmlspecialchars($candidate['kelas']) ?></p>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold text-primary">Visi:</h6>
                                <p><?= nl2br(htmlspecialchars($candidate['visi'])) ?></p>
                                <h6 class="fw-bold text-primary">Misi:</h6>
                                <p><?= nl2br(htmlspecialchars($candidate['misi'])) ?></p>
                                <h6 class="fw-bold text-primary">Program Unggulan:</h6>
                                <p><?= nl2br(htmlspecialchars($candidate['Program'])) ?></p>
                            </div>
                            
                            <form method="POST" class="mt-auto" onsubmit="return confirm('Apakah Anda yakin ingin memilih calon ini? Pilihan Anda tidak bisa diubah setelah ini.');">
                                <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fa-solid fa-check"></i> Pilih Saya
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
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
