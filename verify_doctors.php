<?php
require 'auth/auth_check.php';
ensure_doctor_profile_columns();
ensure_doctor_catalog_tables();
$pdo = db();
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Doctor'");
echo 'doctors=' . $stmt->fetchColumn() . PHP_EOL;
$stmt = $pdo->query('SELECT COUNT(*) FROM hospitals');
echo 'hospitals=' . $stmt->fetchColumn() . PHP_EOL;
$stmt = $pdo->query('SELECT COUNT(*) FROM specializations');
echo 'specializations=' . $stmt->fetchColumn() . PHP_EOL;
