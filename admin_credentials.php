<?php
require_once __DIR__ . '/auth/auth_check.php';
require_auth();

$role = $_SESSION['role'] ?? '';
if ($role !== 'Hospital Admin') {
    redirect('dashboard.php');
}

$errors = session_pull('errors', []);
$success = session_pull('success');

try {
    $stmt = db()->prepare('SELECT id, fullname, email, role, password_hash FROM users WHERE role IN (?, ?, ?, ?) ORDER BY role, fullname');
    $stmt->execute(['Patient', 'Doctor', 'Pharmacist', 'Lab Technician']);
    $accounts = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Unable to load user accounts.';
    $accounts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Credentials - NHRE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/styles.css?v=20260807-13">
</head>
<body class="dashboard-body">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <nav class="dashboard-nav">
    <div class="container d-flex align-items-center justify-content-between gap-3">
      <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
        <img src="assets/images/nhre-logo.svg" alt="NHRE" class="nhre-logo-img">
      </a>
      <div class="d-flex align-items-center gap-2">
        <a href="appointments.php" class="btn btn-outline-light btn-sm">Back to appointments</a>
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
          <span class="auth-kicker">Admin-only access</span>
          <h1>View user account credentials</h1>
          <p>This section is restricted to hospital administrators and should be used only for approved support tasks.</p>
        </div>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-danger auth-alert mt-4" role="alert">
          <?php foreach ($errors as $message): ?>
            <div><?= e($message) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success auth-alert mt-4" role="alert">
          <span><?= e($success) ?></span>
        </div>
      <?php endif; ?>

      <div class="row g-4 mt-3">
        <div class="col-12">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-key"></i></div>
            <h2>Account access list</h2>
            <p class="text-muted">Seeded accounts show their default login password; accounts with a custom password are masked.</p>
            <div class="table-responsive mt-3">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>Role</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Password</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($accounts): ?>
                    <?php foreach ($accounts as $account): ?>
                      <tr>
                        <td><?= e($account['role']) ?></td>
                        <td><?= e($account['fullname']) ?></td>
                        <td><?= e($account['email']) ?></td>
                        <td>
                          <?php
                            $defaultPassword = match ($account['role']) {
                              'Doctor' => 'Doctor123!',
                              'Patient' => 'Patient123!',
                              'Pharmacist' => 'Pharmacist123!',
                              'Lab Technician' => 'Lab123!',
                              default => 'Password123!'
                            };
                            $matchesDefault = !empty($account['password_hash'])
                              && password_verify($defaultPassword, $account['password_hash']);
                          ?>
                          <?php if ($matchesDefault): ?>
                            <?= e($defaultPassword) ?>
                            <span class="badge bg-success-subtle text-success-emphasis">seed</span>
                          <?php else: ?>
                            <span class="text-muted">custom (not shown)</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">No supported accounts found.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </article>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js?v=20260807-5"></script>
</body>
</html>
