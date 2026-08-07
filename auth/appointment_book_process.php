<?php
require_once __DIR__ . '/auth_check.php';
ensure_appointments_table_exists();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../dashboard.php#appointments');
}

if (!csrf_check($_POST['_csrf'] ?? null)) {
    $_SESSION['errors'] = ['Security token expired. Please try again.'];
    redirect('../dashboard.php#appointments');
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role'] ?? '';
if ($user_id <= 0 || $role !== 'Patient') {
    redirect('../dashboard.php');
}

$doctor_id = (int)($_POST['doctor_id'] ?? 0);
$appointment_date = trim((string)($_POST['appointment_date'] ?? ''));
$appointment_time = trim((string)($_POST['appointment_time'] ?? ''));
$reason = trim((string)($_POST['reason'] ?? ''));
$errors = [];

if ($doctor_id <= 0) {
    $errors[] = 'Please select a doctor.';
}

$date = DateTimeImmutable::createFromFormat('Y-m-d', $appointment_date);
if ($appointment_date === '' || $date === false) {
    $errors[] = 'Enter a valid appointment date.';
} elseif ($appointment_date < date('Y-m-d')) {
    $errors[] = 'Appointment date cannot be in the past.';
}

if ($appointment_time === '' || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $appointment_time)) {
    $errors[] = 'Enter a valid appointment time.';
}

if ($reason === '') {
    $errors[] = 'Reason for visit is required.';
} elseif (mb_strlen($reason) > 1000) {
    $errors[] = 'Reason for visit must be 1000 characters or fewer.';
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    redirect('../dashboard.php#appointments');
}

try {
    $stmt = db()->prepare('SELECT id FROM users WHERE id = ? AND role = ? LIMIT 1');
    $stmt->execute([$doctor_id, 'Doctor']);
    if (!$stmt->fetch()) {
        $_SESSION['errors'] = ['Selected doctor is not available.'];
        redirect('../dashboard.php#appointments');
    }

    $stmt = db()->prepare(
        'INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status, doctor_notes, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([$user_id, $doctor_id, $appointment_date, $appointment_time, $reason, 'Pending', '']);

    create_notification(
        $doctor_id,
        'New appointment request',
        'A patient has requested an appointment on ' . $appointment_date . ' at ' . $appointment_time . '.',
        'appointment'
    );

    $_SESSION['success'] = 'Appointment request submitted successfully.';
    redirect('../dashboard.php#appointments');
} catch (PDOException $e) {
    $_SESSION['errors'] = ['Unable to book the appointment. Please try again later.'];
    redirect('../dashboard.php#appointments');
}
