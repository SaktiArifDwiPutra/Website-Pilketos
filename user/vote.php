<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cekuser.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header('Location: ' . BASE_URL . '/masuk.php');
    exit;
}

$stmt = $pdo->prepare("SELECT sudah_vote FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$status = $_GET['status'] ?? '';
$electionStatus = getElectionStatus($pdo);

if ($user && $user['sudah_vote'] && $status !== 'success') {
    header('Location: ' . BASE_URL . '/user/dashboard.php?status=done');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $error = "Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.";
    } elseif (!$electionStatus['is_open']) {
        http_response_code(403);
        $error = $electionStatus['banner_text'];
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

                $voteStmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id, guru_vote) VALUES (?, ?, 0)");
                $voteStmt->execute([$_SESSION['user_id'], $candidate_id]);

                $pdo->commit();

                header('Location: ' . BASE_URL . '/user/vote.php?status=success');
                exit;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($e->getCode() === '23000') {
                    $error = "Anda sudah memberikan suara sebelumnya.";
                } else {
                    error_log('Student vote error: ' . $e->getMessage());
                    $error = "Gagal melakukan voting. Silakan coba lagi.";
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

$page_title = "Pemilihan Ketua OSIS";
include __DIR__ . '/../includes/header.php';
?>
    
    <div class="container my-5">
        <?php if ($status === 'done'): ?>
            <div class="alert alert-info text-center p-4 shadow-sm">
                <h1 class="mb-3">Terima kasih!</h1>
                <p>Anda sudah melakukan voting. Hasil pemilihan akan diumumkan setelah semua siswa memberikan suaranya.</p>
                <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-primary mt-3">Kembali ke Dashboard</a>
            </div>
        <?php elseif ($status === 'success'): ?>
            <div class="alert alert-success text-center p-4 shadow-sm">
                <h1 class="mb-3">Voting Berhasil!</h1>
                <p>Terima kasih telah berpartisipasi dalam pemilihan ketua OSIS.</p>
                <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-success mt-3">Kembali ke Dashboard</a>
            </div>
        <?php elseif (!$electionStatus['is_open']): ?>
            <div class="alert alert-warning text-center p-4 shadow-sm">
                <h1 class="mb-3">Pemungutan Suara Ditutup</h1>
                <p><?= htmlspecialchars($electionStatus['banner_text'], ENT_QUOTES, 'UTF-8') ?></p>
                <a href="<?= BASE_URL ?>/user/dashboard.php" class="btn btn-primary mt-3">Kembali ke Dashboard</a>
            </div>
        <?php else: ?>
            <h1 class="mb-4 text-center">Pemilihan Ketua OSIS</h1>
            <p class="text-center mb-5">Silakan pilih salah satu calon ketua OSIS berikut:</p>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            
            <div class="row g-4">
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
