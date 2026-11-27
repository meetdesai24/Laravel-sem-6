-- =====================================================================
-- Visitor Authentication System - MySQL database file
-- Database : visitor_auth_db     Engine: InnoDB     Charset: utf8mb4
--
-- HOW TO USE (Laragon / HeidiSQL, phpMyAdmin or MySQL command line)
--   1. Create an empty database:  CREATE DATABASE visitor_auth_db;
--   2. Import this file into it.
--   3. In .env set DB_DATABASE=visitor_auth_db  (username root, empty password on Laragon).
--   Do NOT also run "php artisan migrate": this file already records every migration.
--
-- Seeded staff accounts (password for all four is:  password )
--   admin@example.com / security@example.com / host@example.com / host2@example.com
--   Change these passwords before using the system for real.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS otp_verifications, visit_logs, visits, visitors,
    failed_jobs, job_batches, jobs, cache_locks, cache, sessions,
    password_reset_tokens, users, migrations;

-- ---------------------------------------------------------------------
-- Laravel framework tables
-- ---------------------------------------------------------------------
CREATE TABLE migrations (
  id int unsigned NOT NULL AUTO_INCREMENT,
  migration varchar(255) NOT NULL,
  batch int NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- users: Laravel base columns + phone, role, department, status (Table 5.1)
CREATE TABLE users (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  phone varchar(15) DEFAULT NULL,
  email_verified_at timestamp NULL DEFAULT NULL,
  password varchar(255) NOT NULL,
  role enum('admin','security','host') NOT NULL DEFAULT 'host',
  department varchar(100) DEFAULT NULL,
  status tinyint(1) NOT NULL DEFAULT 1,
  remember_token varchar(100) DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
  email varchar(255) NOT NULL,
  token varchar(255) NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
  id varchar(255) NOT NULL,
  user_id bigint unsigned DEFAULT NULL,
  ip_address varchar(45) DEFAULT NULL,
  user_agent text,
  payload longtext NOT NULL,
  last_activity int NOT NULL,
  PRIMARY KEY (id),
  KEY sessions_user_id_index (user_id),
  KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache (
  `key` varchar(255) NOT NULL,
  value mediumtext NOT NULL,
  expiration int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
  `key` varchar(255) NOT NULL,
  owner varchar(255) NOT NULL,
  expiration int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jobs (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  queue varchar(255) NOT NULL,
  payload longtext NOT NULL,
  attempts tinyint unsigned NOT NULL,
  reserved_at int unsigned DEFAULT NULL,
  available_at int unsigned NOT NULL,
  created_at int unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
  id varchar(255) NOT NULL,
  name varchar(255) NOT NULL,
  total_jobs int NOT NULL,
  pending_jobs int NOT NULL,
  failed_jobs int NOT NULL,
  failed_job_ids longtext NOT NULL,
  options mediumtext,
  cancelled_at int DEFAULT NULL,
  created_at int NOT NULL,
  finished_at int DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE failed_jobs (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  uuid varchar(255) NOT NULL,
  connection text NOT NULL,
  queue text NOT NULL,
  payload longtext NOT NULL,
  exception longtext NOT NULL,
  failed_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY failed_jobs_uuid_unique (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Project tables (Tables 5.2 - 5.5)
-- ---------------------------------------------------------------------
CREATE TABLE visitors (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  full_name varchar(100) NOT NULL,
  email varchar(150) NOT NULL,
  phone varchar(15) NOT NULL,
  company varchar(150) DEFAULT NULL,
  address text,
  id_proof_type varchar(30) NOT NULL,
  id_proof_number varchar(255) NOT NULL COMMENT 'encrypted by Laravel',
  photo varchar(255) DEFAULT NULL,
  is_verified tinyint(1) NOT NULL DEFAULT 0,
  is_blacklisted tinyint(1) NOT NULL DEFAULT 0,
  blacklist_reason varchar(255) DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY visitors_email_index (email),
  KEY visitors_phone_index (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE visits (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  visitor_id bigint unsigned NOT NULL,
  host_id bigint unsigned NOT NULL,
  purpose varchar(255) NOT NULL,
  visit_date date NOT NULL,
  expected_in time NOT NULL,
  expected_out time DEFAULT NULL,
  status enum('pending','approved','rejected','checked_in','checked_out','expired') NOT NULL DEFAULT 'pending',
  qr_token char(64) DEFAULT NULL,
  approved_by bigint unsigned DEFAULT NULL,
  remarks varchar(255) DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY visits_qr_token_unique (qr_token),
  KEY visits_visit_date_status_index (visit_date, status),
  CONSTRAINT visits_visitor_id_foreign FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE CASCADE,
  CONSTRAINT visits_host_id_foreign FOREIGN KEY (host_id) REFERENCES users (id),
  CONSTRAINT visits_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE visit_logs (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  visit_id bigint unsigned NOT NULL,
  check_in_time datetime NOT NULL,
  check_out_time datetime DEFAULT NULL,
  checked_in_by bigint unsigned NOT NULL,
  checked_out_by bigint unsigned DEFAULT NULL,
  remarks varchar(255) DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT visit_logs_visit_id_foreign FOREIGN KEY (visit_id) REFERENCES visits (id) ON DELETE CASCADE,
  CONSTRAINT visit_logs_checked_in_by_foreign FOREIGN KEY (checked_in_by) REFERENCES users (id),
  CONSTRAINT visit_logs_checked_out_by_foreign FOREIGN KEY (checked_out_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE otp_verifications (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  visitor_id bigint unsigned NOT NULL,
  otp_hash varchar(255) NOT NULL,
  expires_at datetime NOT NULL,
  attempts tinyint unsigned NOT NULL DEFAULT 0,
  is_used tinyint(1) NOT NULL DEFAULT 0,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT otp_verifications_visitor_id_foreign FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Migration history (so "php artisan migrate" finds nothing left to run)
-- ---------------------------------------------------------------------
INSERT INTO migrations (migration, batch) VALUES
  ('0001_01_01_000000_create_users_table', 1),
  ('0001_01_01_000001_create_cache_table', 1),
  ('0001_01_01_000002_create_jobs_table', 1),
  ('2026_11_26_000100_add_role_fields_to_users_table', 1),
  ('2026_11_26_000101_create_visitors_table', 1),
  ('2026_11_26_000102_create_visits_table', 1),
  ('2026_11_26_000103_create_visit_logs_table', 1),
  ('2026_11_26_000104_create_otp_verifications_table', 1);

-- ---------------------------------------------------------------------
-- Seed data: four staff accounts, password = "password" (bcrypt)
-- ---------------------------------------------------------------------
INSERT INTO users (name, email, phone, password, role, department, status, created_at, updated_at) VALUES
  ('Admin User',    'admin@example.com',    NULL, '$2y$12$42JTAQxXYzE0j/pLc9oB7.9lzvms29WV7RoWwloez65eKLKOL1Sh6', 'admin',    NULL,                   1, NOW(), NOW()),
  ('Gate Security', 'security@example.com', NULL, '$2y$12$42JTAQxXYzE0j/pLc9oB7.9lzvms29WV7RoWwloez65eKLKOL1Sh6', 'security', NULL,                   1, NOW(), NOW()),
  ('Hardik Chavda', 'host@example.com',     NULL, '$2y$12$42JTAQxXYzE0j/pLc9oB7.9lzvms29WV7RoWwloez65eKLKOL1Sh6', 'host',     'Computer Applications', 1, NOW(), NOW()),
  ('Riya Shah',     'host2@example.com',    NULL, '$2y$12$42JTAQxXYzE0j/pLc9oB7.9lzvms29WV7RoWwloez65eKLKOL1Sh6', 'host',     'Administration',        1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;
