<?php
require_once __DIR__ . '/auth/auth_check.php';
require_role(['Patient', 'Doctor']);

$fullname = $_SESSION['fullname'] ?? 'NHRE User';
$role = $_SESSION['role'] ?? 'User';
$errors = session_pull('errors', []);
$success = session_pull('success');

$vaccines = [
    ['name' => 'BCG', 'required_doses' => 1, 'gap_days' => 0, 'description' => 'Protects against tuberculosis.'],
    ['name' => 'DPT', 'required_doses' => 3, 'gap_days' => 28, 'description' => 'Protects against diphtheria, pertussis, and tetanus.'],
    ['name' => 'Polio', 'required_doses' => 4, 'gap_days' => 28, 'description' => 'Protects against poliovirus infection.'],
    ['name' => 'Hepatitis B', 'required_doses' => 3, 'gap_days' => 28, 'description' => 'Protects against hepatitis B infection.'],
    ['name' => 'Measles', 'required_doses' => 2, 'gap_days' => 30, 'description' => 'Protects against measles infection.'],
    ['name' => 'MMR', 'required_doses' => 2, 'gap_days' => 30, 'description' => 'Protects against mumps, measles, and rubella.'],
    ['name' => 'Typhoid', 'required_doses' => 2, 'gap_days' => 14, 'description' => 'Protects against typhoid fever.'],
    ['name' => 'Rabies', 'required_doses' => 3, 'gap_days' => 3, 'description' => 'Rabies vaccine requires 3 doses with 3-day spacing.'],
    ['name' => 'COVID-19', 'required_doses' => 3, 'gap_days' => 28, 'description' => 'Recommended booster schedule for COVID-19 protection.'],
    ['name' => 'Influenza', 'required_doses' => 1, 'gap_days' => 0, 'description' => 'Annual flu protection for seasonal immunity.'],
    ['name' => 'HPV', 'required_doses' => 2, 'gap_days' => 180, 'description' => 'Protects against several human papillomavirus infections.'],
    ['name' => 'Tetanus', 'required_doses' => 3, 'gap_days' => 30, 'description' => 'Recommended booster doses for tetanus prevention.'],
];

try {
    $stmt = db()->prepare('SELECT id, report_type, title, details, uploaded_by, uploaded_at, is_viewed FROM doctor_reports WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 10');
    $stmt->execute([(int)($_SESSION['user_id'] ?? 0)]);
    $reports = $stmt->fetchAll();
} catch (PDOException $e) {
    $reports = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vaccination - NHRE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/styles.css?v=20260807-4">
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
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <nav class="dashboard-nav">
    <div class="container d-flex align-items-center justify-content-between gap-3">
      <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
        <img src="assets/images/nhre-logo.svg" alt="NHRE" class="nhre-logo-img">
      </a>
      <div class="d-flex gap-2">
        <a href="dashboard.php" class="btn btn-dashboard-logout ripple"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
        <a href="notifications.php" class="btn btn-dashboard-logout ripple"><i class="fa-solid fa-bell"></i> <span>Notifications</span></a>
      </div>
    </div>
  </nav>

  <main class="dashboard-main">
    <section class="container">
      <div class="dashboard-hero glass-card">
        <div>
          <span class="auth-kicker">Vaccination Center</span>
          <h1>Vaccination schedule for <?= e($fullname) ?></h1>
          <p>Track routine immunizations, required doses, and dose gaps in one place.</p>
        </div>
        <div class="dashboard-user-pill">
          <i class="fa-solid fa-syringe"></i>
          <span><?= e($role) ?></span>
        </div>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-danger auth-alert" role="alert">
          <i class="fa-solid fa-circle-exclamation"></i>
          <div>
            <?php foreach ($errors as $message): ?>
              <div><?= e($message) ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success auth-alert" role="alert">
          <i class="fa-solid fa-circle-check"></i>
          <span><?= e($success) ?></span>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <?php foreach ($vaccines as $vaccine): ?>
          <div class="col-md-6 col-xl-4">
            <article class="dashboard-card">
              <div class="dashboard-card-icon"><i class="fa-solid fa-shield-virus"></i></div>
              <h2><?= e($vaccine['name']) ?></h2>
              <p><?= e($vaccine['description']) ?></p>
              <div class="mt-3">
                <span class="badge bg-info-subtle text-info-emphasis me-2">Required doses: <?= (int)$vaccine['required_doses'] ?></span>
                <span class="badge bg-secondary-subtle text-secondary-emphasis">Dose gap: <?= (int)$vaccine['gap_days'] ?> day(s)</span>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-4 mt-2">
        <div class="col-12">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-file-medical"></i></div>
            <h2>Doctor Uploaded Reports</h2>
            <?php if ($reports): ?>
              <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th>Report</th>
                      <th>Type</th>
                      <th>Doctor</th>
                      <th>Date</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reports as $report): ?>
                      <tr>
                        <td><?= e($report['title']) ?></td>
                        <td><?= e($report['report_type']) ?></td>
                        <td><?= e($report['uploaded_by']) ?></td>
                        <td><?= e($report['uploaded_at']) ?></td>
                        <td><?= (int)$report['is_viewed'] === 1 ? 'Viewed' : 'New' ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="text-muted mt-3">No doctor reports have been uploaded yet.</p>
            <?php endif; ?>
          </article>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js?v=20260807-3"></script>
</body>
</html>
