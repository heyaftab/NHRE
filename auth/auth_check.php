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

remember_me_login();
