<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../blood_donation.php');
}

if (!csrf_check($_POST['_csrf'] ?? null)) {
    $_SESSION['errors'] = ['Session expired. Please try again.'];
    redirect('../blood_donation.php');
}

$reqName = trim((string)($_POST['requester_name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$bloodGroup = trim((string)($_POST['blood_group'] ?? ''));
$district = trim((string)($_POST['district'] ?? ''));
$notes = trim((string)($_POST['notes'] ?? ''));

if ($reqName === '' || $phone === '' || $bloodGroup === '' || $district === '') {
    $_SESSION['errors'] = ['Please fill all required request fields.'];
    redirect('../blood_donation.php');
}

try {
    $stmt = db()->prepare(
        'INSERT INTO blood_requests (requester_name, phone, blood_group, district, notes)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$reqName, $phone, $bloodGroup, $district, $notes]);

    $_SESSION['success'] = 'Your blood request was submitted. Available donors in your district will be shown below.';
    redirect('../blood_donation.php');
} catch (PDOException $e) {
    $_SESSION['errors'] = ['Something went wrong. Please try again later.'];
    redirect('../blood_donation.php');
}
