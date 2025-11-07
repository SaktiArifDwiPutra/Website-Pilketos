<?php
require_once 'koneksi.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    if (basename($_SERVER['PHP_SELF']) !== 'masuk.php') {
        header('Location: ' . BASE_URL . '/masuk.php');
        exit;
    }
}
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header('Location: ' . BASE_URL . '/masuk.php');
        exit;
    }
    
    $_SESSION['role'] = $user['role'];
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: ../masuk.php');
    exit;
}
?>