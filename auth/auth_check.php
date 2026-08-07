<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function session_start_secure(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

session_start_secure();

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function session_pull(string $key, mixed $default = null): mixed
{
    $value = $_SESSION[$key] ?? $default;
    unset($_SESSION[$key]);
    return $value;
}

function valid_roles(): array
{
    return ['Patient', 'Doctor', 'Pharmacist', 'Lab Technician', 'Hospital Admin'];
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function require_auth(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function redirect_if_authenticated(): void
{
    if (!empty($_SESSION['user_id'])) {
        redirect('dashboard.php');
    }
}

function login_user(array $user, bool $remember = false): void
{
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    if ($remember) {
        $raw = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);

        $stmt = db()->prepare('DELETE FROM auth_tokens WHERE user_id = ?');
        $stmt->execute([(int)$user['id']]);

        $stmt = db()->prepare(
            'INSERT INTO auth_tokens (user_id, token_hash, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))'
        );
        $stmt->execute([(int)$user['id'], $hash, COOKIE_REMEMBER_DAYS]);

        setcookie(COOKIE_REMEMBER, $raw, [
            'expires' => time() + COOKIE_REMEMBER_DAYS * 86400,
            'path' => '/',
            'domain' => '',
            'secure' => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

function remember_me_login(): void
{
    if (!empty($_SESSION['user_id'])) {
        return;
    }

    $raw = $_COOKIE[COOKIE_REMEMBER] ?? null;
    if (!is_string($raw) || $raw === '') {
        return;
    }

    try {
        $stmt = db()->prepare(
            'SELECT t.user_id, u.fullname, u.email, u.role
             FROM auth_tokens t
             JOIN users u ON u.id = t.user_id
             WHERE t.token_hash = ? AND t.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([hash('sha256', $raw)]);
        $user = $stmt->fetch();

        if ($user) {
            login_user($user);
        }
    } catch (PDOException $e) {
    }
}

function unread_notification_count(int $user_id): int
{
    try {
        $stmt = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

function create_notification(int $user_id, string $title, string $message, string $type = 'general'): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO notifications (user_id, title, message, notification_type)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$user_id, $title, $message, $type]);
    } catch (PDOException $e) {
    }
}

function ensure_appointments_table_exists(): void
{
    db()->exec(
        'CREATE TABLE IF NOT EXISTS `appointments` (
          `appointment_id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `patient_id`       INT UNSIGNED NOT NULL,
          `doctor_id`        INT UNSIGNED NOT NULL,
          `appointment_date` DATE            NOT NULL,
          `appointment_time` TIME            NOT NULL,
          `reason`           TEXT            NOT NULL,
          `status`           VARCHAR(30)     NOT NULL DEFAULT \'Pending\',
          `doctor_notes`     TEXT            NULL,
          `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`appointment_id`),
          KEY `idx_appointments_patient` (`patient_id`),
          KEY `idx_appointments_doctor` (`doctor_id`),
          CONSTRAINT `fk_appointments_patient`
            FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `fk_appointments_doctor`
            FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );
}

function ensure_medical_test_tables_exists(): void
{
    db()->exec(
        'CREATE TABLE IF NOT EXISTS `medical_tests` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    db()->exec(
        'CREATE TABLE IF NOT EXISTS `medical_test_bookings` (
          `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `test_id`           INT UNSIGNED NOT NULL,
          `user_id`           INT UNSIGNED NOT NULL,
          `booking_date`      DATE NOT NULL,
          `booking_time`      TIME NULL,
          `status`            VARCHAR(30) NOT NULL DEFAULT \'Pending\',
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
    );

    $count = (int)db()->query('SELECT COUNT(*) FROM medical_tests')->fetchColumn();
    if ($count === 0) {
        $seed = [
            ['CBC Test', 'A complete blood count check for overall health screening.', 'Blood Test', 500, 'Dhaka', 'Pathology', 'Same Day', 1, 1],
            ['Blood Sugar Test', 'Checks fasting and random glucose levels for diabetes screening.', 'Biochemistry', 300, 'Mirpur', 'Lab', 'Same Day', 1, 0],
            ['Lipid Profile', 'Measures cholesterol and triglyceride levels to assess heart risk.', 'Biochemistry', 700, 'Uttara', 'Biochemistry', '1 Day', 1, 1],
            ['X-Ray', 'Radiology imaging service for bones and chest evaluation.', 'Imaging', 1200, 'Dhanmondi', 'Radiology', '2 Days', 1, 0],
            ['COVID/Flu Test', 'Rapid screening for COVID-19 and flu-related symptoms.', 'COVID/Flu Test', 900, 'Banani', 'Pathology', 'Same Day', 1, 1],
            ['Thyroid Profile', 'Measures thyroid hormones for metabolic and hormonal balance.', 'Hormone Test', 850, 'Gulshan', 'Endocrinology', '1 Day', 1, 0],
        ];

        $stmt = db()->prepare(
            'INSERT INTO medical_tests (name, description, test_type, price, place, department, result_time, availability, home_collection) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($seed as $row) {
            $stmt->execute($row);
        }
    }
}

remember_me_login();
