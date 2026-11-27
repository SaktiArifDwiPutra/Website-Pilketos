-- Migration: Add configurable election_settings table (replaces hardcoded 2025 dates).

CREATE TABLE IF NOT EXISTS `election_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `election_title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pemilihan Wakil & Ketua OSIS',
  `voting_start` datetime NULL DEFAULT NULL,
  `voting_end` datetime NULL DEFAULT NULL,
  `is_voting_open` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `election_settings` (`id`, `election_title`, `voting_start`, `voting_end`, `is_voting_open`)
SELECT 1, 'Pemilihan Wakil & Ketua OSIS', NULL, NULL, 1
WHERE NOT EXISTS (SELECT 1 FROM `election_settings` WHERE `id` = 1);

