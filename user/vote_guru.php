<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cekuser.php';

if ($_SESSION['role'] !== 'guru') {
    header('Location: ' . BASE_URL . '/user/dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT sudah_vote FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userRow = $stmt->fetch();

$voteCheckStmt = $pdo->prepare("SELECT id FROM votes WHERE user_id = ?");
$voteCheckStmt->execute([$_SESSION['user_id']]);
$already_voted = ($userRow && $userRow['sudah_vote']) || (bool) $voteCheckStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = "Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.";
    } elseif ($already_voted) {
        $error = "Anda sudah memberikan suara sebelumnya.";
    } else {
        $candidate_id = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);

        if (!$candidate_id || $candidate_id <= 0) {
            $error = "Kandidat yang dipilih tidak valid.";
        } else {
            try {
                $pdo->beginTransaction();

                $candidateStmt = $pdo->prepare("SELECT id FROM candidates WHERE id = ? LIMIT 1");
                $candidateStmt->execute([$candidate_id]);
                if (!$candidateStmt->fetch()) {
                    throw new RuntimeException("Kandidat tidak ditemukan.");
                }

                $updateStmt = $pdo->prepare("UPDATE users SET sudah_vote = 1 WHERE id = ? AND sudah_vote = 0");
                $updateStmt->execute([$_SESSION['user_id']]);
                if ($updateStmt->rowCount() !== 1) {
                    throw new RuntimeException("Anda sudah memberikan suara sebelumnya.");
                }

                $voteStmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id, guru_vote) VALUES (?, ?, 1)");
                $voteStmt->execute([$_SESSION['user_id'], $candidate_id]);

                $pdo->commit();
                header('Location: ' . BASE_URL . '/user/dashboard.php?status=success');
                exit;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($e->getCode() === '23000') {
                    $error = "Anda sudah memberikan suara sebelumnya.";
                } else {
                    error_log('Teacher vote error: ' . $e->getMessage());
                    $error = "Gagal menyimpan pilihan. Silakan coba lagi.";
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = $e->getMessage();
            }
        }
    }
}

$stmt = $pdo->query("SELECT * FROM candidates");
$candidates = $stmt->fetchAll();

$page_title = "Pemilihan Ketua OSIS - Guru";
include __DIR__ . '/../includes/header.php';
?>
    
    <div class="container py-4">
        <h1 class="mb-3">Pemilihan Ketua OSIS</h1>
        <p class="text-muted">Sebagai Guru, Anda berhak memberikan suara untuk memilih ketua OSIS.</p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
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
                            <img src="<?= BASE_URL ?>/assets/img/candidates/<?= htmlspecialchars($candidate['foto'], ENT_QUOTES, 'UTF-8') ?>" 
                                 class="card-img-top"
                                 alt="<?= htmlspecialchars($candidate['nama'], ENT_QUOTES, 'UTF-8') ?>"
                                 onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/img/default.jpg'">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h3 class="card-title"><?= htmlspecialchars($candidate['nama'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="text-muted mb-3">Kelas: <?= htmlspecialchars($candidate['kelas'], ENT_QUOTES, 'UTF-8') ?></p>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold text-primary">Visi:</h6>
                                <p><?= nl2br(htmlspecialchars($candidate['visi'], ENT_QUOTES, 'UTF-8')) ?></p>
                                <h6 class="fw-bold text-primary">Misi:</h6>
                                <p><?= nl2br(htmlspecialchars($candidate['misi'], ENT_QUOTES, 'UTF-8')) ?></p>
                                <h6 class="fw-bold text-primary">Program Unggulan:</h6>
                                <p><?= nl2br(htmlspecialchars($candidate['Program'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                            
                            <form method="POST" class="mt-auto" onsubmit="return confirm('Apakah Anda yakin ingin memilih calon ini? Pilihan Anda tidak bisa diubah setelah ini.');">
                                <?= csrfField() ?>
                                <input type="hidden" name="candidate_id" value="<?= (int) $candidate['id'] ?>">
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
        document.querySelector('a[href*="keluar.php"]')?.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar?')) {
                e.preventDefault();
            }
        });
    </script>
