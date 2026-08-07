<?php
require_once __DIR__ . '/auth/auth_check.php';
require_auth();

$fullname = $_SESSION['fullname'] ?? 'NHRE User';
$email = $_SESSION['email'] ?? '';
$role = $_SESSION['role'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - NHRE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="dashboard-body">
  <nav class="dashboard-nav">
    <div class="container d-flex align-items-center justify-content-between gap-3">
      <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
        <span class="brand-mark">NH</span>
        <span class="brand-name">NHRE<span>.</span></span>
      </a>
      <a href="logout.php" class="btn btn-dashboard-logout ripple">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        <span>Logout</span>
      </a>
    </div>
  </nav>

  <main class="dashboard-main">
    <section class="container">
      <div class="dashboard-hero glass-card">
        <div>
          <span class="auth-kicker">Authenticated Dashboard</span>
          <h1>Welcome,<br><?= e($fullname) ?></h1>
          <p>Role: <strong><?= e($role) ?></strong></p>
        </div>
        <div class="dashboard-user-pill">
          <i class="fa-solid fa-circle-user"></i>
          <span><?= e($email) ?></span>
        </div>
      </div>

      <div class="row g-4 mt-1">
        <div class="col-md-6 col-xl-3">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-user"></i></div>
            <h2>Profile</h2>
            <p>View and manage your verified NHRE identity details.</p>
            <a href="#" class="dashboard-card-link">Open Profile</a>
          </article>
        </div>

        <div class="col-md-6 col-xl-3">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-notes-medical"></i></div>
            <h2>Medical Records</h2>
            <p>Placeholder area for prescriptions, lab results, and visit history.</p>
            <a href="#" class="dashboard-card-link">Coming Soon</a>
          </article>
        </div>

        <div class="col-md-6 col-xl-3">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <h2>Appointments</h2>
            <p>Placeholder area for appointments and care-team scheduling.</p>
            <a href="#" class="dashboard-card-link">Coming Soon</a>
          </article>
        </div>

        <div class="col-md-6 col-xl-3">
          <article class="dashboard-card dashboard-card-danger">
            <div class="dashboard-card-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
            <h2>Logout</h2>
            <p>End this protected session and return to the login page.</p>
            <a href="logout.php" class="dashboard-card-link">Logout Securely</a>
          </article>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js"></script>
</body>
</html>
