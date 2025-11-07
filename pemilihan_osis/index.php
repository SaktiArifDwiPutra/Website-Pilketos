<?php
require_once __DIR__ . '/includes/koneksi.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/user/dashboard.php');
    }
    exit;
} else {
    header('Location: ' . BASE_URL . '/masuk.php');
}
exit;
?>