<?php
/**
 * Migration: Enforce one vote per user via UNIQUE index on votes(user_id).
 * Inspects existing records first and aborts without modifying data if duplicates exist.
 */

return static function (PDO $pdo): void {
    // Check if a unique index already exists on votes.user_id
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'votes'
          AND COLUMN_NAME = 'user_id'
          AND NON_UNIQUE = 0
    ");
    if ((int) $stmt->fetchColumn() > 0) {
        echo "[INFO] Unique constraint on votes(user_id) already exists.\n";
        return;
    }

    // Inspect for duplicate votes before altering schema
    $dups = $pdo->query("
        SELECT user_id, COUNT(*) AS vote_count
        FROM `votes`
        GROUP BY `user_id`
        HAVING COUNT(*) > 1
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($dups)) {
        $summary = implode(', ', array_map(
            static fn(array $row): string => "user_id={$row['user_id']} ({$row['vote_count']} votes)",
            $dups
        ));
        throw new RuntimeException(
            "Cannot add unique constraint on votes(user_id): duplicate vote records found ({$summary}). " .
            "Resolve duplicates manually before re-running migrations."
        );
    }

    $pdo->exec("ALTER TABLE `votes` ADD UNIQUE KEY `uq_votes_user_id` (`user_id`)");
};

