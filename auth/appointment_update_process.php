<?php
require_once __DIR__ . '/auth_check.php';
ensure_appointments_table_exists();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../appointments.php');
}

if (!csrf_check($_POST['_csrf'] ?? null)) {
    $_SESSION['errors'] = ['Security token expired. Please try again.'];
    redirect('../appointments.php');
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role'] ?? '';
$appointment_id = (int)($_POST['appointment_id'] ?? 0);
$action = trim((string)($_POST['action'] ?? ''));
$doctor_notes = trim((string)($_POST['doctor_notes'] ?? ''));
$status = trim((string)($_POST['status'] ?? ''));

$allowed_statuses = ['Pending', 'Approved', 'Completed', 'Cancelled', 'Rejected'];
if ($appointment_id <= 0) {
    $_SESSION['errors'] = ['Invalid appointment request.'];
    redirect('../appointments.php');
}

try {
    if ($role === 'Doctor') {
        $stmt = db()->prepare('SELECT patient_id, doctor_id, status FROM appointments WHERE appointment_id = ? AND doctor_id = ? LIMIT 1');
        $stmt->execute([$appointment_id, $user_id]);
        $appointment = $stmt->fetch();
        if (!$appointment) {
            $_SESSION['errors'] = ['Appointment not found or access denied.'];
            redirect('../appointments.php');
        }

        if ($action === 'delete') {
            $_SESSION['errors'] = ['Doctors cannot delete appointments.'];
            redirect('../appointments.php');
        }

        if ($status !== '' && !in_array($status, ['Approved', 'Rejected', 'Completed'], true)) {
            $_SESSION['errors'] = ['Invalid appointment action.'];
            redirect('../appointments.php');
        }

        if ($status !== '') {
            $updateStmt = db()->prepare('UPDATE appointments SET status = ?, doctor_notes = ? WHERE appointment_id = ?');
            $updateStmt->execute([$status, $doctor_notes, $appointment_id]);
            create_notification(
                (int)$appointment['patient_id'],
                'Appointment ' . $status,
                'Your appointment has been ' . strtolower($status) . ' by the doctor.',
                'appointment'
            );
            $_SESSION['success'] = 'Appointment status updated successfully.';
        } else {
            $updateStmt = db()->prepare('UPDATE appointments SET doctor_notes = ? WHERE appointment_id = ?');
            $updateStmt->execute([$doctor_notes, $appointment_id]);
            $_SESSION['success'] = 'Doctor notes saved successfully.';
        }

        redirect('../appointments.php');
    }

    if ($role === 'Hospital Admin') {
        $stmt = db()->prepare('SELECT patient_id, doctor_id FROM appointments WHERE appointment_id = ? LIMIT 1');
        $stmt->execute([$appointment_id]);
        $appointment = $stmt->fetch();
        if (!$appointment) {
            $_SESSION['errors'] = ['Appointment not found.'];
            redirect('../appointments.php');
        }

        if ($action === 'delete') {
            $deleteStmt = db()->prepare('DELETE FROM appointments WHERE appointment_id = ?');
            $deleteStmt->execute([$appointment_id]);

            create_notification(
                (int)$appointment['patient_id'],
                'Appointment deleted',
                'An administrator removed your appointment request.',
                'appointment'
            );
            create_notification(
                (int)$appointment['doctor_id'],
                'Appointment deleted',
                'An administrator removed an appointment assigned to you.',
                'appointment'
            );

            $_SESSION['success'] = 'Appointment deleted successfully.';
            redirect('../appointments.php');
        }

        if (!in_array($status, $allowed_statuses, true)) {
            $_SESSION['errors'] = ['Invalid appointment status.'];
            redirect('../appointments.php');
        }

        $updateStmt = db()->prepare('UPDATE appointments SET status = ?, doctor_notes = ? WHERE appointment_id = ?');
        $updateStmt->execute([$status, $doctor_notes, $appointment_id]);

        create_notification(
            (int)$appointment['patient_id'],
            'Appointment updated',
            'An administrator updated the appointment status to ' . $status . '.',
            'appointment'
        );

        $_SESSION['success'] = 'Appointment updated successfully.';
        redirect('../appointments.php');
    }

    $_SESSION['errors'] = ['You do not have permission to update appointments.'];
    redirect('../appointments.php');
} catch (PDOException $e) {
    $_SESSION['errors'] = ['Unable to update appointment. Please try again later.'];
    redirect('../appointments.php');
}
