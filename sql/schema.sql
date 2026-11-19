-- Schéma MySQL /MariaDB du vérificateur d'anagrammes.
-- Configuration attendue : utilisateur root, mot de passe vide (XAMPP).

CREATE DATABASE IF NOT EXISTS anagrammes_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE anagrammes_db;

DROP TABLE IF EXISTS verifications;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pseudo        VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE verifications (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NULL,
    guest_token  VARCHAR(64) NULL,
    string1      VARCHAR(255) NOT NULL,
    string2      VARCHAR(255) NOT NULL,
    is_anagram   TINYINT(1) NOT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_verifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comptes de démonstration (mot de passe : password123, stocké haché).
INSERT INTO users (id, pseudo, password_hash) VALUES
    (1, 'O lyrhes', '$2y$12$c0XXlTkH5wcMf9CyUSvDFut13EuLmkbuTOQDpxWEkts/JfhbevKgS'),
    (2, 'testuser', '$2y$12$c0XXlTkH5wcMf9CyUSvDFut13EuLmkbuTOQDpxWEkts/JfhbevKgS');

-- Historique de démonstration (identique à la capture 06-historique.png).
INSERT INTO verifications (id, user_id, guest_token, string1, string2, is_anagram, created_at) VALUES
    (1, 1, NULL, 'velo',    'vole',    1, '2026-11-19 09:00:00'),
    (2, 1, NULL, 'cat',     'atc',     1, '2026-11-19 09:01:00'),
    (3, 1, NULL, 'ahh',     'ahh',     1, '2026-11-19 09:02:00'),
    (4, 1, NULL, 'rouge',   'vert',    0, '2026-11-19 09:03:00'),
    (5, 1, NULL, 'velo',    'vole',    1, '2026-11-19 09:04:00'),
    (6, 1, NULL, 'cat',     'atc',     1, '2026-11-19 09:05:00'),
    (7, 1, NULL, 'qwerty',  'ytrewq',  1, '2026-11-19 09:06:00'),
    (8, 1, NULL, 'rat',     'car',     0, '2026-11-19 09:07:00'),
    (9, 1, NULL, 'anagram', 'nagaram', 1, '2026-11-19 09:08:00');
