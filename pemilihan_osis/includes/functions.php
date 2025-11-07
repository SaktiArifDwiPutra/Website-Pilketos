<?php
require_once 'koneksi.php';

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isGuru() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'guru';
}

function isSiswa() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'siswa';
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header('Location: ../masuk.php');
        exit;
    }
}

function redirectIfNotLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../masuk.php');
        exit;
    }
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function requireAdmin() {
    redirectIfNotLoggedIn();
    redirectIfNotAdmin();
}
?>