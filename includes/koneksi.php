<?php
require_once __DIR__ . '/env.php';

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$appDebug = filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
if ($appDebug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

try {
    $pdo = createPdoConnection();
} catch (PDOException $e) {
    if ($appDebug) {
        die('Koneksi database gagal: ' . $e->getMessage());
    }
    error_log('Database connection failed: ' . $e->getMessage());
    die('Koneksi database gagal. Silakan hubungi administrator.');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim((string) env('BASE_URL', 'http://localhost/pemilihan_osis'), '/'));
}
