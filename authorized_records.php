<?php
require_once __DIR__ . '/auth/auth_check.php';
require_role(['Doctor', 'Lab Technician', 'Pharmacist']);
ensure_access_tables_exists();

$providerId = (int)($_SESSION['user_id'] ?? 0);
$patientId = (int)($_GET['patient'] ?? 0);

$access = $patientId > 0 ? active_access($patientId, $providerId) : null;
if ($patientId <= 0 || $access === null) {
    $_SESSION['errors'] = ['You do not have an active authorization to view this patient\u2019s records.'];
    redirect('patient_search.php');
}

try {
    $stmt = db()->prepare(
        'SELECT id, fullname, nid, email, phone, date_of_birth, gender, blood_group, account_number
         FROM users WHERE id = ?'
    );
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch();
} catch (PDOException $e) {
    $patient = false;
}

if (!$patient) {
    $_SESSION['errors'] = ['Patient could not be found.'];
    redirect('patient_search.php');
}

$recordTypes = array_values(array_filter(array_map('trim', explode(',', $access['record_types']))));
$loggedAccess = $_SESSION['logged_access'][$patientId] ?? false;

if (!$loggedAccess) {
    foreach ($recordTypes as $index => $type) {
        log_record_access($access['id'], $patientId, $providerId, $type, $index === 0);
    }
    $_SESSION['logged_access'][$patientId] = true;
}

$fullname = $_SESSION['fullname'] ?? 'NHRE User';
$errors = session_pull('errors', []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Authorized Records - NHRE</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/styles.css?v=20260807-13">
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
  <?php require __DIR__ . '/includes/topnav.php'; ?>
  <main class="dashboard-main">
    <section class="container">
      <div class="dashboard-hero glass-card">
        <div>
          <span class="auth-kicker">Authorized Records</span>
          <h1><?= e($patient['fullname']) ?></h1>
          <p>Access granted by the patient until <?= e(date('j M Y', strtotime($access['expires_at']))) ?>. All views are logged and visible to the patient.</p>
        </div>
        <div class="dashboard-user-pill">
          <i class="fa-solid fa-file-shield"></i>
          <span>Patient ID #<?= (int)$patient['id'] ?></span>
        </div>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-danger auth-alert mt-4" role="alert">
          <i class="fa-solid fa-circle-exclamation"></i>
          <div>
            <?php foreach ($errors as $message): ?>
              <div><?= e($message) ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="row g-4 mt-1">
        <div class="col-lg-4">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-address-card"></i></div>
            <h2>Basic Information</h2>
            <p class="mb-1"><strong>Name:</strong> <?= e($patient['fullname']) ?></p>
            <p class="mb-1"><strong>Age:</strong> <?= $patient['date_of_birth'] ? e((string)age_from_dob($patient['date_of_birth'])) . ' years' : '—' ?></p>
            <p class="mb-1"><strong>Gender:</strong> <?= $patient['gender'] ? e($patient['gender']) : '—' ?></p>
            <p class="mb-0"><strong>Blood Group:</strong> <?= $patient['blood_group'] ? e($patient['blood_group']) : '—' ?></p>
          </article>
        </div>

        <div class="col-lg-8">
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-folder-open"></i></div>
            <h2>Authorized Record Types</h2>
            <?php if ($recordTypes): ?>
              <div class="row g-3 mt-1">
                <?php foreach ($recordTypes as $type): ?>
                  <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-file-lines text-teal"></i>
                        <strong><?= e($type) ?></strong>
                      </div>
                      <p class="mb-0 text-muted small">This record type is authorized but is not part of the current prototype data model. It will appear here once the corresponding module is implemented.</p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-muted mt-2">The patient has not authorized any specific record types yet.</p>
            <?php endif; ?>
            <p class="small text-muted mt-3 mb-0">
              <i class="fa-solid fa-circle-info"></i>
              Your access to this record was logged and the patient was notified.
            </p>
          </article>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js?v=20260807-5"></script>
</body>
</html>
