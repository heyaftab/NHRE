<?php
declare(strict_types=1);

session_start();
$_SESSION = [];

if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params['path'],
    $params['domain'],
    $params['secure'],
    $params['httponly']
  );
}

session_destroy();
session_start();
$_SESSION['success'] = 'You have been logged out safely.';

header('Location: login.php');
exit;
