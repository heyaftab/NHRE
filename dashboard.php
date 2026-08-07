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
$appointments = [];
$filter_patient = trim((string)($_GET['patient'] ?? ''));
$filter_doctor = (int)($_GET['doctor'] ?? 0);
$filter_status = trim((string)($_GET['status'] ?? ''));
$filter_date = trim((string)($_GET['date'] ?? ''));

try {
    if ($role === 'Patient') {
        $stmt = db()->prepare('SELECT id, fullname FROM users WHERE role = ? ORDER BY fullname ASC');
        $stmt->execute(['Doctor']);
        $doctor_list = $stmt->fetchAll();

        $stmt = db()->prepare(
            'SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status, a.doctor_notes, u.fullname AS doctor_name
             FROM appointments a
             JOIN users u ON u.id = a.doctor_id
             WHERE a.patient_id = ?
             ORDER BY a.appointment_date ASC, a.appointment_time ASC'
        );
        $stmt->execute([(int)($_SESSION['user_id'] ?? 0)]);
        $appointments = $stmt->fetchAll();
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
            <a href="#appointments" class="dashboard-card-link">Open Appointments</a>
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

      <section class="mt-4" id="appointments">
        <div class="dashboard-hero glass-card">
          <div>
            <span class="auth-kicker">Appointment Management</span>
            <h1>Manage your appointments</h1>
            <p>Book consultations, review schedules, and control appointment status based on your NHRE role.</p>
          </div>
          <div class="dashboard-user-pill">
            <i class="fa-solid fa-calendar-check"></i>
            <span><?= e($role) ?></span>
          </div>
        </div>

        <?php if ($role === 'Patient'): ?>
          <div class="row g-4 mt-3">
            <div class="col-lg-5">
              <article class="dashboard-card">
                <div class="dashboard-card-icon"><i class="fa-solid fa-calendar-plus"></i></div>
                <h2>Book Appointment</h2>
                <p>Choose a doctor, date, time, and reason to request a new consultation.</p>
                <form action="auth/appointment_book_process.php" method="POST" class="mt-3">
                  <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                  <div class="mb-3">
                    <label class="form-label">Doctor</label>
                    <select class="form-select" name="doctor_id" required>
                      <option value="">Select a doctor</option>
                      <?php foreach ($doctor_list as $doctor): ?>
                        <option value="<?= e($doctor['id']) ?>"><?= e($doctor['fullname']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Appointment Date</label>
                    <input type="date" class="form-control" name="appointment_date" min="<?= e($today) ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Appointment Time</label>
                    <input type="time" class="form-control" name="appointment_time" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Reason for Visit</label>
                    <textarea class="form-control" name="reason" rows="4" maxlength="1000" required></textarea>
                  </div>
                  <button type="submit" class="btn btn-solid-nhre w-100">Submit Appointment</button>
                </form>
              </article>
            </div>

            <div class="col-lg-7">
              <article class="dashboard-card">
                <div class="dashboard-card-icon"><i class="fa-solid fa-clock"></i></div>
                <h2>My Appointments</h2>
                <p>Review requests, appointment status, and doctor comments.</p>
                <div class="table-responsive mt-3">
                  <table class="table table-hover align-middle">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Doctor Notes</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if ($appointments): ?>
                        <?php foreach ($appointments as $appointment): ?>
                          <tr>
                            <td><?= e($appointment['appointment_id']) ?></td>
                            <td><?= e($appointment['doctor_name']) ?></td>
                            <td><?= e($appointment['appointment_date']) ?></td>
                            <td><?= e($appointment['appointment_time']) ?></td>
                            <td><?= e($appointment['reason']) ?></td>
                            <td><span class="badge <?= $appointment['status'] === 'Pending' ? 'bg-warning-subtle text-warning-emphasis' : ($appointment['status'] === 'Approved' ? 'bg-info-subtle text-info-emphasis' : ($appointment['status'] === 'Completed' ? 'bg-success-subtle text-success-emphasis' : ($appointment['status'] === 'Cancelled' ? 'bg-danger-subtle text-danger-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'))) ?>"><?= e($appointment['status']) ?></span></td>
                            <td><?= e($appointment['doctor_notes']) ?></td>
                            <td>
                              <?php if (in_array($appointment['status'], ['Pending', 'Approved'], true)): ?>
                                <form action="auth/appointment_cancel_process.php" method="POST" class="d-inline">
                                  <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                  <input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_id']) ?>">
                                  <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                                </form>
                              <?php else: ?>
                                <span class="text-muted">N/A</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="8" class="text-center text-muted">No appointments found.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </article>
            </div>
          </div>
        <?php elseif ($role === 'Doctor'): ?>
          <div class="row g-4 mt-3">
            <div class="col-12">
              <article class="dashboard-card">
                <div class="dashboard-card-icon"><i class="fa-solid fa-stethoscope"></i></div>
                <h2>Assigned Appointments</h2>
                <p>Approve, reject, complete, or add notes for appointments assigned to you.</p>
                <div class="table-responsive mt-3">
                  <table class="table table-hover align-middle">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Doctor Notes</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if ($appointments): ?>
                        <?php foreach ($appointments as $appointment): ?>
                          <tr>
                            <td><?= e($appointment['appointment_id']) ?></td>
                            <td><?= e($appointment['patient_name']) ?></td>
                            <td><?= e($appointment['appointment_date']) ?></td>
                            <td><?= e($appointment['appointment_time']) ?></td>
                            <td><?= e($appointment['reason']) ?></td>
                            <td><span class="badge <?= $appointment['status'] === 'Pending' ? 'bg-warning-subtle text-warning-emphasis' : ($appointment['status'] === 'Approved' ? 'bg-info-subtle text-info-emphasis' : ($appointment['status'] === 'Completed' ? 'bg-success-subtle text-success-emphasis' : ($appointment['status'] === 'Cancelled' ? 'bg-danger-subtle text-danger-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'))) ?>"><?= e($appointment['status']) ?></span></td>
                            <td>
                              <form action="auth/appointment_update_process.php" method="POST" class="d-flex flex-column gap-2">
                                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_id']) ?>">
                                <textarea class="form-control form-control-sm" name="doctor_notes" rows="2" placeholder="Add notes"><?= e($appointment['doctor_notes']) ?></textarea>
                                <div class="d-flex gap-2 flex-wrap">
                                  <button type="submit" name="status" value="Approved" class="btn btn-outline-success btn-sm">Approve</button>
                                  <button type="submit" name="status" value="Rejected" class="btn btn-outline-danger btn-sm">Reject</button>
                                  <button type="submit" name="status" value="Completed" class="btn btn-outline-primary btn-sm">Complete</button>
                                  <button type="submit" name="status" value="" class="btn btn-outline-secondary btn-sm">Save Notes</button>
                                </div>
                              </form>
                            </td>
                            <td></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="8" class="text-center text-muted">No appointments assigned to you.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </article>
            </div>
          </div>
        <?php elseif ($role === 'Hospital Admin'): ?>
          <div class="row g-4 mt-3">
            <div class="col-12">
              <article class="dashboard-card">
                <div class="dashboard-card-icon"><i class="fa-solid fa-hospital"></i></div>
                <h2>All Appointments</h2>
                <p>Search, filter, update status, and delete appointments across the system.</p>
                <form action="dashboard.php" method="GET" class="row g-3 align-items-end mt-3">
                  <div class="col-md-3">
                    <label class="form-label">Patient</label>
                    <input type="text" class="form-control" name="patient" value="<?= e($filter_patient) ?>" placeholder="Patient name">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Doctor</label>
                    <select class="form-select" name="doctor">
                      <option value="0">All doctors</option>
                      <?php foreach ($doctor_list as $doctor): ?>
                        <option value="<?= e($doctor['id']) ?>" <?= $filter_doctor === (int)$doctor['id'] ? 'selected' : '' ?>><?= e($doctor['fullname']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                      <option value="">All statuses</option>
                      <?php foreach ($appointment_statuses as $statusOption): ?>
                        <option value="<?= e($statusOption) ?>" <?= $filter_status === $statusOption ? 'selected' : '' ?>><?= e($statusOption) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="date" value="<?= e($filter_date) ?>">
                  </div>
                  <div class="col-md-2">
                    <button type="submit" class="btn btn-solid-nhre w-100">Apply Filter</button>
                  </div>
                </form>

                <div class="table-responsive mt-3">
                  <table class="table table-hover align-middle">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Doctor Notes</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if ($appointments): ?>
                        <?php foreach ($appointments as $appointment): ?>
                          <tr>
                            <td><?= e($appointment['appointment_id']) ?></td>
                            <td><?= e($appointment['patient_name']) ?></td>
                            <td><?= e($appointment['doctor_name']) ?></td>
                            <td><?= e($appointment['appointment_date']) ?></td>
                            <td><?= e($appointment['appointment_time']) ?></td>
                            <td><span class="badge <?= $appointment['status'] === 'Pending' ? 'bg-warning-subtle text-warning-emphasis' : ($appointment['status'] === 'Approved' ? 'bg-info-subtle text-info-emphasis' : ($appointment['status'] === 'Completed' ? 'bg-success-subtle text-success-emphasis' : ($appointment['status'] === 'Cancelled' ? 'bg-danger-subtle text-danger-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'))) ?>"><?= e($appointment['status']) ?></span></td>
                            <td>
                              <form action="auth/appointment_update_process.php" method="POST" class="d-flex flex-column gap-2">
                                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_id']) ?>">
                                <textarea class="form-control form-control-sm" name="doctor_notes" rows="2" placeholder="Doctor notes"><?= e($appointment['doctor_notes']) ?></textarea>
                                <input type="hidden" name="status" value="<?= e($appointment['status']) ?>">
                                <button type="submit" name="action" value="update" class="btn btn-outline-primary btn-sm">Save Notes</button>
                              </form>
                            </td>
                            <td class="d-flex gap-2 flex-wrap">
                              <form action="auth/appointment_update_process.php" method="POST">
                                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_id']) ?>">
                                <input type="hidden" name="status" value="Approved">
                                <button type="submit" name="action" value="update" class="btn btn-outline-success btn-sm">Approve</button>
                              </form>
                              <form action="auth/appointment_update_process.php" method="POST">
                                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_id']) ?>">
                                <input type="hidden" name="status" value="Rejected">
                                <button type="submit" name="action" value="update" class="btn btn-outline-danger btn-sm">Reject</button>
                              </form>
                              <form action="auth/appointment_update_process.php" method="POST">
                                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_id']) ?>">
                                <input type="hidden" name="status" value="Completed">
                                <button type="submit" name="action" value="update" class="btn btn-outline-secondary btn-sm">Complete</button>
                              </form>
                              <form action="auth/appointment_update_process.php" method="POST">
                                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="appointment_id" value="<?= e($appointment['appointment_id']) ?>">
                                <button type="submit" name="action" value="delete" class="btn btn-outline-danger btn-sm">Delete</button>
                              </form>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="8" class="text-center text-muted">No appointments found.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </article>
            </div>
          </div>
        <?php elseif ($role === 'Pharmacist' || $role === 'Lab Technician'): ?>
          <div class="row g-4 mt-3">
            <div class="col-12">
              <article class="dashboard-card">
                <div class="dashboard-card-icon"><i class="fa-solid fa-ban"></i></div>
                <h2>Appointment Management</h2>
                <p>Appointment management is not available for <?= e($role) ?>s.</p>
              </article>
            </div>
          </div>
        <?php else: ?>
          <div class="row g-4 mt-3">
            <div class="col-12">
              <article class="dashboard-card">
                <div class="dashboard-card-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <h2>Appointment Management</h2>
                <p>Your role does not have appointment access at this time.</p>
              </article>
            </div>
          </div>
        <?php endif; ?>
      </section>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js?v=20260807-2"></script>
</body>
</html>
