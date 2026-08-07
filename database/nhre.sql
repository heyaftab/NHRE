-- =====================================================
-- NHRE - National Healthcare Record Exchange
-- Database schema for MySQL (run via phpMyAdmin or CLI):
--   mysql -u root -p < database/schema.sql
-- =====================================================

CREATE DATABASE IF NOT EXISTS `nhre`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `nhre`;

-- -----------------------------------------------------
-- Registered accounts
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`                INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `fullname`          VARCHAR(150)     NOT NULL,
  `nid`               VARCHAR(20)      NOT NULL,
  `email`             VARCHAR(190)     NOT NULL,
  `phone`             VARCHAR(20)      NOT NULL,
  `password_hash`     VARCHAR(255)     NOT NULL,
  `role`              VARCHAR(50)      NOT NULL DEFAULT 'Patient',
  `account_number`    VARCHAR(50)      NULL DEFAULT NULL,
  `date_of_birth`     DATE             NULL DEFAULT NULL,
  `nationality`       VARCHAR(100)     NULL DEFAULT NULL,
  `gender`            VARCHAR(20)      NULL DEFAULT NULL,
  `address`           TEXT             NULL DEFAULT NULL,
  `emergency_contact` VARCHAR(100)     NULL DEFAULT NULL,
  `blood_group`       VARCHAR(10)      NULL DEFAULT NULL,
  `marital_status`    VARCHAR(30)      NULL DEFAULT NULL,
  `occupation`        VARCHAR(100)     NULL DEFAULT NULL,
  `profile_photo`     VARCHAR(255)     NULL DEFAULT NULL,
  `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_nid`    (`nid`),
  UNIQUE KEY `uq_users_email`  (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Failed login attempts (brute-force / rate limiting)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(190)  NOT NULL,
  `ip_address`   VARCHAR(45)   NOT NULL,
  `attempted_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_lookup` (`email`, `ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- "Remember me" authentication tokens
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `auth_tokens` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED  NOT NULL,
  `token_hash` CHAR(64)      NOT NULL,
  `expires_at` DATETIME      NOT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_auth_tokens_hash` (`token_hash`),
  KEY `idx_auth_tokens_user` (`user_id`),
  CONSTRAINT `fk_auth_tokens_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Password reset links
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`      INT UNSIGNED  NOT NULL,
  `token_hash`   CHAR(64)      NOT NULL,
  `expires_at`   DATETIME      NOT NULL,
  `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_password_resets_hash` (`token_hash`),
  KEY `idx_password_resets_user` (`user_id`),
  CONSTRAINT `fk_password_resets_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Blood donation donors and requests
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `blood_donors` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`             INT UNSIGNED NOT NULL,
  `blood_group`         VARCHAR(10)  NOT NULL,
  `phone`               VARCHAR(20)  NOT NULL,
  `district`            VARCHAR(100) NOT NULL,
  `available`           TINYINT(1)   NOT NULL DEFAULT 1,
  `last_donation_date`  DATETIME     NULL DEFAULT NULL,
  `next_eligible_date`  DATETIME     NULL DEFAULT NULL,
  `created_at`          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_blood_donors_user` (`user_id`),
  CONSTRAINT `fk_blood_donors_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blood_requests` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `requester_name`  VARCHAR(150) NOT NULL,
  `phone`           VARCHAR(20)  NOT NULL,
  `blood_group`     VARCHAR(10)  NOT NULL,
  `district`        VARCHAR(100) NOT NULL,
  `notes`           TEXT         NULL,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Vaccination and notification modules
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `doctor_reports` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED NOT NULL,
  `report_type`   VARCHAR(100) NOT NULL,
  `title`         VARCHAR(190) NOT NULL,
  `details`       TEXT         NULL,
  `uploaded_by`   VARCHAR(150) NOT NULL,
  `uploaded_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_viewed`     TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_doctor_reports_user` (`user_id`),
  CONSTRAINT `fk_doctor_reports_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`           INT UNSIGNED NOT NULL,
  `title`             VARCHAR(190) NOT NULL,
  `message`           TEXT         NOT NULL,
  `notification_type` VARCHAR(100) NOT NULL,
  `created_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read`           TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`),
  CONSTRAINT `fk_notifications_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Appointment scheduling
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `appointments` (
  `appointment_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id`       INT UNSIGNED NOT NULL,
  `doctor_id`        INT UNSIGNED NOT NULL,
  `appointment_date` DATE            NOT NULL,
  `appointment_time` TIME            NOT NULL,
  `reason`           TEXT            NOT NULL,
  `status`           VARCHAR(30)     NOT NULL DEFAULT 'Pending',
  `doctor_notes`     TEXT            NULL,
  `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`appointment_id`),
  KEY `idx_appointments_patient` (`patient_id`),
  KEY `idx_appointments_doctor` (`doctor_id`),
  CONSTRAINT `fk_appointments_patient`
    FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_appointments_doctor`
    FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Medical test marketplace
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `medical_tests` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(190) NOT NULL,
  `description`       TEXT NULL,
  `test_type`         VARCHAR(100) NOT NULL,
  `price`             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `place`             VARCHAR(120) NOT NULL,
  `department`        VARCHAR(120) NULL,
  `result_time`       VARCHAR(60) NOT NULL,
  `availability`      TINYINT(1) NOT NULL DEFAULT 1,
  `home_collection`   TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medical_tests_place` (`place`),
  KEY `idx_medical_tests_type` (`test_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `medical_test_bookings` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `test_id`           INT UNSIGNED NOT NULL,
  `user_id`           INT UNSIGNED NOT NULL,
  `booking_date`      DATE NOT NULL,
  `booking_time`      TIME NULL,
  `status`            VARCHAR(30) NOT NULL DEFAULT 'Pending',
  `result_file`       VARCHAR(255) NULL,
  `result_notes`      TEXT NULL,
  `result_date`       DATE NULL,
  `technician_id`     INT UNSIGNED NULL,
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_test_bookings_user` (`user_id`),
  KEY `idx_test_bookings_test` (`test_id`),
  CONSTRAINT `fk_test_bookings_test`
    FOREIGN KEY (`test_id`) REFERENCES `medical_tests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_test_bookings_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_test_bookings_technician`
    FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Optional demo account (password: Admin123!)
--   email:    admin@nhre.gov
--   fullname: System Administrator
--   role:     Hospital Admin
-- Uncomment to seed a default admin:
-- -----------------------------------------------------
-- INSERT INTO `users` (`fullname`, `nid`, `email`, `phone`, `password_hash`, `role`)
-- VALUES ('System Administrator', '0000000000', 'admin@nhre.gov', '+8801000000000',
--         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hospital Admin');
