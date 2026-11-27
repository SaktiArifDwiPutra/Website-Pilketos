<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['user_id'])) {
    if (basename($_SERVER['PHP_SELF']) !== 'masuk.php') {
        header('Location: ' . BASE_URL . '/masuk.php');
        exit;
    }
    return;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . BASE_URL . '/masuk.php');
    exit;
}

$_SESSION['role'] = $user['role'];
