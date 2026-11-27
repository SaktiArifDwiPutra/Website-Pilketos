<?php
/**
 * PHP CLI Migration Runner for Pilketos Web App.
 *
 * Usage:
 *   php database/migrate.php
 *   php database/migrate.php --db=pemilihan_osis_test --create-db
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only be run from the command line.\n");
}

require_once __DIR__ . '/../includes/env.php';

$dbOverride = null;
$createDb = false;

foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--db=')) {
        $dbOverride = substr($arg, 5);
    } elseif ($arg === '--create-db') {
        $createDb = true;
    }
}

$targetDb = $dbOverride ?: (string) env('DB_DATABASE', 'pemilihan_osis');
$host = (string) env('DB_HOST', 'localhost');
$port = (string) env('DB_PORT', '3306');
$username = (string) env('DB_USERNAME', 'root');
$password = (string) env('DB_PASSWORD', '');
$charset = (string) env('DB_CHARSET', 'utf8mb4');

try {
    if ($createDb) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $targetDb)) {
            throw new RuntimeException("Invalid database name: {$targetDb}");
        }
        $rootPdo = new PDO("mysql:host={$host};port={$port};charset={$charset}", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$targetDb}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
        echo "[INFO] Ensured database '{$targetDb}' exists.\n";
    }

    $pdo = createPdoConnection($targetDb);
    echo "[INFO] Connected to database '{$targetDb}'.\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `schema_migrations` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `version` VARCHAR(255) NOT NULL,
            `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_schema_migrations_version` (`version`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $applied = $pdo->query("SELECT version FROM schema_migrations")->fetchAll(PDO::FETCH_COLUMN);
    $appliedMap = array_flip($applied);

    $migrationsDir = __DIR__ . '/migrations';
    $files = glob($migrationsDir . '/*.{sql,php}', GLOB_BRACE);
    if ($files === false) {
        throw new RuntimeException("Failed to scan migrations directory: {$migrationsDir}");
    }
    sort($files, SORT_STRING);

    if (empty($files)) {
        echo "[INFO] No migration files found.\n";
        exit(0);
    }

    $appliedCount = 0;

    foreach ($files as $filePath) {
        $version = basename($filePath);
        if (isset($appliedMap[$version])) {
            echo "[SKIP] {$version} (already applied)\n";
            continue;
        }

        echo "[APPLY] {$version}...\n";
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        try {
            $pdo->beginTransaction();

            if ($ext === 'sql') {
                $sql = file_get_contents($filePath);
                if ($sql === false) {
                    throw new RuntimeException("Unable to read SQL file: {$filePath}");
                }
                // Strip full-line SQL comments before splitting into statements
                $cleanSql = preg_replace('/^\s*--.*$/m', '', $sql);
                $statements = array_filter(
                    array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', (string) $cleanSql)),
                    static fn(string $s): bool => $s !== ''
                );
                foreach ($statements as $statement) {
                    $pdo->exec($statement);
                }
            } elseif ($ext === 'php') {
                $migration = require $filePath;
                if (!is_callable($migration)) {
                    throw new RuntimeException("PHP migration {$version} must return a callable.");
                }
                $migration($pdo);
            }

            $stmt = $pdo->prepare("INSERT INTO `schema_migrations` (`version`) VALUES (?)");
            $stmt->execute([$version]);

            if ($pdo->inTransaction()) {
                $pdo->commit();
            }

            echo "[OK] {$version} applied successfully.\n";
            $appliedCount++;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            fwrite(STDERR, "[ERROR] Migration {$version} failed: " . $e->getMessage() . "\n");
            exit(1);
        }
    }

    echo "[DONE] Migrations complete. {$appliedCount} migration(s) applied.\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[ERROR] " . $e->getMessage() . "\n");
    exit(1);
}
