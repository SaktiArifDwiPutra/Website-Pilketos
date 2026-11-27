<?php
/**
 * Idempotent Initial Admin Seeder.
 *
 * Usage:
 *   php database/seeds/001_admin_seeder.php
 *   php database/seeds/001_admin_seeder.php --db=pemilihan_osis_test
 *
 * Configuration:
 *   Set SEED_ADMIN_USERNAME and SEED_ADMIN_PASSWORD in .env.
 *   If SEED_ADMIN_PASSWORD is empty, a random 16-character password is generated
 *   and printed once to the CLI output (never stored in plaintext).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only be run from the command line.\n");
}

require_once __DIR__ . '/../../includes/env.php';

$dbOverride = null;
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--db=')) {
        $dbOverride = substr($arg, 5);
    }
}

$targetDb = $dbOverride ?: (string) env('DB_DATABASE', 'pemilihan_osis');
$adminUsername = trim((string) env('SEED_ADMIN_USERNAME', 'admin'));
$adminPassword = (string) env('SEED_ADMIN_PASSWORD', '');

if ($adminUsername === '') {
    fwrite(STDERR, "[ERROR] SEED_ADMIN_USERNAME cannot be empty.\n");
    exit(1);
}

try {
    $pdo = createPdoConnection($targetDb);

    $stmt = $pdo->prepare("SELECT id, role FROM `users` WHERE `username` = ? LIMIT 1");
    $stmt->execute([$adminUsername]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo "[SKIP] User '{$adminUsername}' already exists (id={$existing['id']}, role={$existing['role']}).\n";
        exit(0);
    }

    $generated = false;
    if ($adminPassword === '') {
        $adminPassword = bin2hex(random_bytes(8));
        $generated = true;
    }

    $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT);

    $insert = $pdo->prepare("
        INSERT INTO `users` (`username`, `password`, `role`, `sudah_vote`)
        VALUES (?, ?, 'admin', 0)
    ");
    $insert->execute([$adminUsername, $hashedPassword]);

    echo "[OK] Admin user '{$adminUsername}' created successfully in '{$targetDb}'.\n";
    if ($generated) {
        echo "[IMPORTANT] Generated one-time initial password: {$adminPassword}\n";
        echo "[IMPORTANT] Save this password and change it immediately after logging in.\n";
    } else {
        echo "[INFO] Initial password was read from SEED_ADMIN_PASSWORD in environment.\n";
    }

    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[ERROR] Seeder failed: " . $e->getMessage() . "\n");
    exit(1);
}

