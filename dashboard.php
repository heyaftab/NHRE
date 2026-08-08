<?php
require_once __DIR__ . '/auth/auth_check.php';
require_auth();
ensure_appointments_table_exists();

$fullname = $_SESSION['fullname'] ?? 'NHRE User';
$email = $_SESSION['email'] ?? '';
$role = $_SESSION['role'] ?? 'User';
$errors = session_pull('errors', []);
$success = session_pull('success');

$today = date('Y-m-d');
$appointment_statuses = ['Pending', 'Approved', 'Completed', 'Cancelled', 'Rejected'];
$doctor_list = [];
$selected_doctor = null;
$appointments = [];
$doctor_accounts = [];
$filter_patient = trim((string)($_GET['patient'] ?? ''));
$filter_doctor = (int)($_GET['doctor'] ?? 0);
$filter_status = trim((string)($_GET['status'] ?? ''));
$filter_date = trim((string)($_GET['date'] ?? ''));
$doctor_search = trim((string)($_GET['doctor_search'] ?? ''));
$doctor_district = trim((string)($_GET['doctor_district'] ?? ''));
$doctor_hospital = trim((string)($_GET['doctor_hospital'] ?? ''));
$doctor_specialization = trim((string)($_GET['doctor_specialization'] ?? ''));
$selected_doctor_id = (int)($_GET['doctor_id'] ?? 0);
$show_all_doctors = isset($_GET['view_all_doctors']) && $_GET['view_all_doctors'] === '1';

try {
    ensure_doctor_profile_columns();
    ensure_doctor_catalog_tables();

    if ($role === 'Patient') {
        $stmt = db()->prepare(
            'SELECT u.id, u.fullname, u.district, u.hospital_name, u.specialization, u.qualification, u.experience_years, u.consultation_fee, u.rating, u.reviews_count, u.address, u.bio, u.visiting_hours, u.awards, u.is_featured,
                    d.name AS district_name, h.name AS hospital_name_db, s.name AS specialization_name
             FROM users u
             LEFT JOIN districts d ON d.id = u.district_id
             LEFT JOIN hospitals h ON h.id = u.hospital_id
             LEFT JOIN specializations s ON s.id = u.specialization_id
             WHERE u.role = ?
             ORDER BY u.fullname ASC'
        );
        $stmt->execute(['Doctor']);
        $doctor_rows = $stmt->fetchAll();

        $doctor_list = array_map(function (array $doctor): array {
            $doctor['district'] = $doctor['district'] ?: ($doctor['district_name'] ?? '');
            $doctor['hospital_name'] = $doctor['hospital_name'] ?: ($doctor['hospital_name_db'] ?? '');
            $doctor['specialization'] = $doctor['specialization'] ?: ($doctor['specialization_name'] ?? '');
            return $doctor;
        }, $doctor_rows);

        $doctor_min_rating = (float)($_GET['doctor_min_rating'] ?? 0);
        $doctor_min_experience = (int)($_GET['doctor_min_experience'] ?? 0);
        $doctor_min_fee = (int)($_GET['doctor_min_fee'] ?? 0);
        $doctor_max_fee = (int)($_GET['doctor_max_fee'] ?? 0);

        if ($doctor_search !== '') {
            $needle = '%' . $doctor_search . '%';
            $doctor_list = array_values(array_filter($doctor_list, function ($doctor) use ($needle): bool {
                return strpos((string)($doctor['fullname'] ?? ''), $doctor_search) !== false
                    || strpos((string)($doctor['specialization'] ?? ''), $doctor_search) !== false
                    || strpos((string)($doctor['hospital_name'] ?? ''), $doctor_search) !== false
                    || strpos((string)($doctor['district'] ?? ''), $doctor_search) !== false;
            }));
        }
        if ($doctor_district !== '') {
            $doctor_list = array_values(array_filter($doctor_list, function ($doctor) use ($doctor_district): bool {
                return (string)($doctor['district'] ?? '') === $doctor_district;
            }));
        }
        if ($doctor_hospital !== '') {
            $doctor_list = array_values(array_filter($doctor_list, function ($doctor) use ($doctor_hospital): bool {
                return (string)($doctor['hospital_name'] ?? '') === $doctor_hospital;
            }));
        }
        if ($doctor_specialization !== '') {
            $doctor_list = array_values(array_filter($doctor_list, function ($doctor) use ($doctor_specialization): bool {
                return (string)($doctor['specialization'] ?? '') === $doctor_specialization;
            }));
        }
        if ($doctor_min_rating > 0) {
            $doctor_list = array_values(array_filter($doctor_list, function ($doctor) use ($doctor_min_rating): bool {
                return (float)($doctor['rating'] ?? 0) >= $doctor_min_rating;
            }));
        }
        if ($doctor_min_experience > 0) {
            $doctor_list = array_values(array_filter($doctor_list, function ($doctor) use ($doctor_min_experience): bool {
                return (int)($doctor['experience_years'] ?? 0) >= $doctor_min_experience;
            }));
        }
        if ($doctor_min_fee > 0) {
            $doctor_list = array_values(array_filter($doctor_list, function ($doctor) use ($doctor_min_fee): bool {
                return (int)($doctor['consultation_fee'] ?? 0) >= $doctor_min_fee;
            }));
        }
        if ($doctor_max_fee > 0) {
            $doctor_list = array_values(array_filter($doctor_list, function ($doctor) use ($doctor_max_fee): bool {
                return (int)($doctor['consultation_fee'] ?? 0) <= $doctor_max_fee;
            }));
        }

        if ($selected_doctor_id > 0) {
            foreach ($doctor_list as $doctor) {
                if ((int)$doctor['id'] === $selected_doctor_id) {
                    $selected_doctor = $doctor;
                    break;
                }
            }
        }

        $featured_doctors = array_values(array_filter($doctor_rows, static function ($doctor): bool {
            return !empty($doctor['rating']) || !empty($doctor['reviews_count']) || !empty($doctor['experience_years']);
        }));
        usort($featured_doctors, function ($a, $b): int {
            $ratingDiff = ((float)($b['rating'] ?? 0) - (float)($a['rating'] ?? 0));
            if ($ratingDiff !== 0.0) {
                return $ratingDiff > 0 ? 1 : -1;
            }
            $reviewDiff = ((int)($b['reviews_count'] ?? 0) - (int)($a['reviews_count'] ?? 0));
            if ($reviewDiff !== 0) {
                return $reviewDiff > 0 ? 1 : -1;
            }
            return ((int)($b['experience_years'] ?? 0) - (int)($a['experience_years'] ?? 0));
        });
        $featured_doctors = array_slice($featured_doctors, 0, 3);
        foreach ($featured_doctors as &$featured) {
            $featured['district'] = $featured['district'] ?: ($featured['district_name'] ?? '');
            $featured['hospital_name'] = $featured['hospital_name'] ?: ($featured['hospital_name_db'] ?? '');
            $featured['specialization'] = $featured['specialization'] ?: ($featured['specialization_name'] ?? '');
        }
        unset($featured);

        $stmt = db()->prepare(
            'SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status, a.doctor_notes, u.fullname AS doctor_name
             FROM appointments a
             JOIN users u ON u.id = a.doctor_id
             WHERE a.patient_id = ?
             ORDER BY a.appointment_date ASC, a.appointment_time ASC'
        );
        $stmt->execute([(int)($_SESSION['user_id'] ?? 0)]);
        $appointments = $stmt->fetchAll();

        $doctor_accounts_stmt = db()->prepare('SELECT id, fullname, email FROM users WHERE role = ? ORDER BY id ASC');
        $doctor_accounts_stmt->execute(['Doctor']);
        $doctor_accounts = $doctor_accounts_stmt->fetchAll();
    } elseif ($role === 'Doctor') {
        $stmt = db()->prepare(
            'SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status, a.doctor_notes, u.fullname AS patient_name
             FROM appointments a
             JOIN users u ON u.id = a.patient_id
             WHERE a.doctor_id = ?
             ORDER BY a.appointment_date ASC, a.appointment_time ASC'
        );
        $stmt->execute([(int)($_SESSION['user_id'] ?? 0)]);
        $appointments = $stmt->fetchAll();
    } elseif ($role === 'Hospital Admin') {
        $stmt = db()->prepare('SELECT id, fullname FROM users WHERE role = ? ORDER BY fullname ASC');
        $stmt->execute(['Doctor']);
        $doctor_list = $stmt->fetchAll();

        $sql = 
            'SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status, a.doctor_notes, pd.fullname AS patient_name, dd.fullname AS doctor_name
             FROM appointments a
             JOIN users pd ON pd.id = a.patient_id
             JOIN users dd ON dd.id = a.doctor_id';
        $conditions = [];
        $params = [];

        if ($filter_patient !== '') {
            $conditions[] = 'pd.fullname LIKE ?';
            $params[] = '%' . $filter_patient . '%';
        }

        if ($filter_doctor > 0) {
            $conditions[] = 'dd.id = ?';
            $params[] = $filter_doctor;
        }

        if (in_array($filter_status, $appointment_statuses, true)) {
            $conditions[] = 'a.status = ?';
            $params[] = $filter_status;
        }

        if ($filter_date !== '' && DateTimeImmutable::createFromFormat('Y-m-d', $filter_date) !== false) {
            $conditions[] = 'a.appointment_date = ?';
            $params[] = $filter_date;
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY a.appointment_date ASC, a.appointment_time ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $appointments = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $errors[] = 'Unable to load appointment data. Please try again later.';
}
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

  document.addEventListener('DOMContentLoaded', function () {
    if (location.hash === '#doctor-profile') {
      var profileSection = document.getElementById('doctor-profile');
      if (profileSection) {
        setTimeout(function () {
          profileSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 80);
      }
    }
  });
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

      <?php if ($success): ?>
        <div class="alert alert-success auth-alert mt-4" role="alert">
          <i class="fa-solid fa-circle-check"></i>
          <span><?= e($success) ?></span>
        </div>
      <?php endif; ?>

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
            <div class="dashboard-card-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <h2>Appointments</h2>
            <p>Book, approve, and review appointments directly from your dashboard.</p>
            <a href="appointments.php" class="dashboard-card-link">Open Appointments</a>
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
          <article class="dashboard-card">
            <div class="dashboard-card-icon"><i class="fa-solid fa-flask-vial"></i></div>
            <h2>Tests</h2>
            <p>Browse medical tests, book diagnostics, and track your lab bookings in one place.</p>
            <a href="medical_tests.php" class="dashboard-card-link">Open Marketplace</a>
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

      <section class="mt-4">
        <div class="dashboard-hero glass-card">
          <div>
            <span class="auth-kicker">Appointment Workspace</span>
            <h1>Open the dedicated appointment page</h1>
            <p>Use the full appointment workspace to search doctors, book visits, and manage requests.</p>
          </div>
          <div class="dashboard-user-pill">
            <i class="fa-solid fa-calendar-check"></i>
            <span><?= e($role) ?></span>
          </div>
        </div>

        <div class="row g-4 mt-1">
          <div class="col-12">
            <article class="dashboard-card">
              <div class="dashboard-card-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></div>
              <h2>Continue to appointments</h2>
              <p>Booking and management actions now happen on the dedicated appointment page.</p>
              <a href="appointments.php" class="btn btn-solid-nhre mt-2">Open Appointment Page</a>
            </article>
          </div>
        </div>
      </section>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js?v=20260807-2"></script>
</body>
</html>
