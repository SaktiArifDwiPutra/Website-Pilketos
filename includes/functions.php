<?php
require_once __DIR__ . '/koneksi.php';

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
        header('Location: ' . BASE_URL . '/masuk.php');
        exit;
    }
}

function redirectIfNotLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/masuk.php');
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

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    $token = htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function verifyCsrfToken(?string $token): bool {
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        return false;
    }
    if (!is_string($token) || $token === '') {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function getElectionStatus(PDO $pdo): array {
    $default = [
        'election_title' => 'Pemilihan Wakil & Ketua OSIS',
        'voting_start' => null,
        'voting_end' => null,
        'is_voting_open' => 1,
        'is_open' => true,
        'schedule_text' => 'Sedang Berlangsung',
        'banner_text' => 'Pemilihan sedang berlangsung. Pastikan Anda sudah memberikan suara!',
    ];

    try {
        $stmt = $pdo->query("SELECT election_title, voting_start, voting_end, is_voting_open FROM election_settings WHERE id = 1 LIMIT 1");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        if (!$row) {
            return $default;
        }
    } catch (Throwable $e) {
        // Fallback if migration 003 hasn't been applied yet
        return $default;
    }

    $now = time();
    $startTs = !empty($row['voting_start']) ? strtotime($row['voting_start']) : null;
    $endTs = !empty($row['voting_end']) ? strtotime($row['voting_end']) : null;
    $manualOpen = (int) $row['is_voting_open'] === 1;

    $isOpen = $manualOpen;
    $bannerText = 'Pemilihan sedang berlangsung. Pastikan Anda sudah memberikan suara!';

    if (!$manualOpen) {
        $isOpen = false;
        $bannerText = 'Pemungutan suara saat ini sedang ditutup oleh administrator.';
    } elseif ($startTs !== null && $now < $startTs) {
        $isOpen = false;
        $bannerText = 'Pemungutan suara akan dibuka pada ' . date('d M Y H:i', $startTs) . '.';
    } elseif ($endTs !== null && $now > $endTs) {
        $isOpen = false;
        $bannerText = 'Pemungutan suara telah ditutup pada ' . date('d M Y H:i', $endTs) . '.';
    } elseif ($endTs !== null) {
        $bannerText = 'Pemilihan akan ditutup pada ' . date('d M Y H:i', $endTs) . '. Pastikan Anda sudah memberikan suara!';
    }

    if ($startTs !== null && $endTs !== null) {
        $scheduleText = date('d M Y', $startTs) . ' – ' . date('d M Y', $endTs);
    } elseif ($endTs !== null) {
        $scheduleText = 's/d ' . date('d M Y', $endTs);
    } elseif ($startTs !== null) {
        $scheduleText = 'Mulai ' . date('d M Y', $startTs);
    } else {
        $scheduleText = $manualOpen ? 'Sedang Dibuka' : 'Ditutup';
    }

    return [
        'election_title' => $row['election_title'],
        'voting_start' => $row['voting_start'],
        'voting_end' => $row['voting_end'],
        'is_voting_open' => (int) $row['is_voting_open'],
        'is_open' => $isOpen,
        'schedule_text' => $scheduleText,
        'banner_text' => $bannerText,
    ];
}
