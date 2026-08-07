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
  <link rel="stylesheet" href="assets/css/styles.css?v=20260807-2">
<script>
  (function () {
    try {
      var t = localStorage.getItem("nhre-theme");
      if (t !== "light" && t !== "dark") {
        t = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
      }
      document.documentElement.dataset.theme = t;
      document.documentElement.style.colorScheme = t;
    } catch (e) {}
  })();
</script>
</head>
<body class="dashboard-body">
  <nav class="dashboard-nav">
    <div class="container d-flex align-items-center justify-content-between gap-3">
      <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
        <img src="assets/images/nhre-logo.svg" alt="NHRE" class="nhre-logo-img">
      </a>
      <div class="d-flex align-items-center gap-2">
        <div class="notification-wrap" id="notificationWrap">
          <button type="button" class="notification-icon-button ripple" id="notificationBell" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-badge" id="notificationBadge" hidden></span>
          </button>
          <div class="notification-overlay" id="notificationOverlay" hidden>
            <input type="hidden" id="notificationCsrf" value="<?= csrf_token() ?>">
            <div class="notification-overlay-head">
              <strong>Notifications</strong>
              <button type="button" class="notification-mark-read" id="markAllRead">Mark all read</button>
            </div>
            <div class="notification-overlay-list" id="notificationList"></div>
            <a href="notifications.php" class="notification-overlay-footer">View all notifications</a>
          </div>
        </div>
        <a href="logout.php" class="btn btn-dashboard-logout ripple">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
          <span>Logout</span>
        </a>
      </div>
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
            <a href="profile.php" class="dashboard-card-link">Open Profile</a>
          </article>
        </div>

        <div class="col-md-6 col-xl-3">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-syringe"></i></div>
            <h2>Vaccination</h2>
            <p>Track vaccine schedules, required doses, and doctor reports in one place.</p>
            <a href="vaccination.php" class="dashboard-card-link">Open Vaccination</a>
          </article>
        </div>

        <div class="col-md-6 col-xl-3">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-bell"></i></div>
            <h2>Notifications</h2>
            <p>Review medical approvals, blood donation updates, and profile alerts.</p>
            <a href="notifications.php" class="dashboard-card-link">Open Notifications</a>
          </article>
        </div>

        <div class="col-md-6 col-xl-3">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-pills"></i></div>
            <h2>Pharmacy</h2>
            <p>Browse medicines, check availability, and request pharmacy support.</p>
            <a href="pharmacy.php" class="dashboard-card-link">Open Pharmacy</a>
          </article>
        </div>

        <div class="col-md-6 col-xl-3">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-droplet"></i></div>
            <h2>Blood Donation</h2>
            <p>Register as a donor, request blood, and view available donors by district.</p>
            <a href="blood_donation.php" class="dashboard-card-link">Open Blood Donation</a>
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
  <script src="assets/js/app.js?v=20260807-2"></script>
</body>
</html>
