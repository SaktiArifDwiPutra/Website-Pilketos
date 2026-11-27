<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cekuser.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        header('Location: dashboard.php?error=Token+keamanan+tidak+valid');
        exit;
    }

    $current_pass = (string) ($_POST['current_password'] ?? '');
    $new_pass = (string) ($_POST['new_password'] ?? '');
    $confirm_pass = (string) ($_POST['confirm_password'] ?? '');

    if (strlen($new_pass) < 6) {
        header('Location: dashboard.php?error=Password+baru+minimal+6+karakter');
        exit;
    }

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: dashboard.php?error=Akun+pengguna+tidak+ditemukan');
        exit;
    }

    if (password_verify($current_pass, $user['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_password = password_hash($new_pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
            header('Location: dashboard.php?success=1');
            exit;
        } else {
            header('Location: dashboard.php?error=Password+baru+tidak+cocok');
            exit;
        }
    } else {
        header('Location: dashboard.php?error=Password+saat+ini+salah');
        exit;
    }
}

header('Location: dashboard.php');
exit;
