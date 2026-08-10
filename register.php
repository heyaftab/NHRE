<?php
require_once __DIR__ . '/auth/auth_check.php';
redirect_if_authenticated();

$errors = session_pull('errors', []);
$old = session_pull('old', []);
$roles = self_service_roles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - NHRE</title>
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
<body class="auth-body">
  <main class="auth-page">
    <a class="auth-brand" href="index.php" aria-label="Back to NHRE home">
      <img src="assets/images/nhre-logo.svg" alt="NHRE" class="nhre-logo-img">
    </a>

    <section class="auth-shell container">
      <div class="row g-4 align-items-center justify-content-center">
        <div class="col-xl-5 d-none d-xl-block">
          <div class="auth-info-panel">
            <div class="auth-info-icon"><i class="fa-solid fa-notes-medical"></i></div>
            <h1>Create one verified healthcare identity.</h1>
            <p>Register as a patient or provider stakeholder to join the National Healthcare Record Exchange.</p>
            <div class="auth-info-list">
              <span><i class="fa-solid fa-id-card"></i> NID verified account</span>
              <span><i class="fa-solid fa-user-doctor"></i> Healthcare role access</span>
              <span><i class="fa-solid fa-file-shield"></i> Secure medical data</span>
            </div>
          </div>
        </div>

        <div class="col-xl-7 col-lg-9">
          <div class="card auth-card glass-card">
            <div class="card-body">
              <div class="auth-card-head">
                <span class="auth-kicker">Create account</span>
                <h2>Register for NHRE</h2>
                <p>All fields are required for a secure healthcare identity.</p>
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

              <form action="auth/register_process.php" method="POST" class="needs-validation auth-form" id="registerForm" novalidate>
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Full Name" value="<?= e($old['fullname'] ?? '') ?>" required minlength="2" maxlength="150">
                      <label for="fullname"><i class="fa-solid fa-user"></i> Full Name</label>
                      <div class="invalid-feedback">Full name is required.</div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="nid" name="nid" placeholder="National ID" value="<?= e($old['nid'] ?? '') ?>" required pattern="[0-9]{10,20}">
                      <label for="nid"><i class="fa-solid fa-id-card"></i> National ID (NID)</label>
                      <div class="invalid-feedback">Enter a 10 to 20 digit National ID.</div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?= e($old['email'] ?? '') ?>" required>
                      <label for="email"><i class="fa-solid fa-envelope"></i> Email</label>
                      <div class="invalid-feedback">Enter a valid email address.</div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="tel" class="form-control" id="phone" name="phone" placeholder="+880..." value="<?= e($old['phone'] ?? '') ?>" required pattern="^\+?[0-9][0-9\s().-]{7,19}$">
                      <label for="phone"><i class="fa-solid fa-phone"></i> Phone Number</label>
                      <div class="invalid-feedback">Enter a valid phone number.</div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="account_number" name="account_number" placeholder="NHRE-1001" value="<?= e($old['account_number'] ?? '') ?>">
                      <label for="account_number"><i class="fa-solid fa-hashtag"></i> Account Number</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?= e($old['date_of_birth'] ?? '') ?>">
                      <label for="date_of_birth"><i class="fa-solid fa-calendar-day"></i> Date of Birth</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="nationality" name="nationality" placeholder="Bangladeshi" value="<?= e($old['nationality'] ?? '') ?>">
                      <label for="nationality"><i class="fa-solid fa-globe"></i> Nationality</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <select class="form-select" id="gender" name="gender">
                        <option value="" <?= empty($old['gender']) ? 'selected' : '' ?>>Select gender</option>
                        <option value="Male" <?= (($old['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= (($old['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= (($old['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                      </select>
                      <label for="gender"><i class="fa-solid fa-venus-mars"></i> Gender</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <select class="form-select" id="blood_group" name="blood_group">
                        <option value="" <?= empty($old['blood_group']) ? 'selected' : '' ?>>Select blood group</option>
                        <option value="A+" <?= (($old['blood_group'] ?? '') === 'A+') ? 'selected' : '' ?>>A+</option>
                        <option value="A-" <?= (($old['blood_group'] ?? '') === 'A-') ? 'selected' : '' ?>>A-</option>
                        <option value="B+" <?= (($old['blood_group'] ?? '') === 'B+') ? 'selected' : '' ?>>B+</option>
                        <option value="B-" <?= (($old['blood_group'] ?? '') === 'B-') ? 'selected' : '' ?>>B-</option>
                        <option value="AB+" <?= (($old['blood_group'] ?? '') === 'AB+') ? 'selected' : '' ?>>AB+</option>
                        <option value="AB-" <?= (($old['blood_group'] ?? '') === 'AB-') ? 'selected' : '' ?>>AB-</option>
                        <option value="O+" <?= (($old['blood_group'] ?? '') === 'O+') ? 'selected' : '' ?>>O+</option>
                        <option value="O-" <?= (($old['blood_group'] ?? '') === 'O-') ? 'selected' : '' ?>>O-</option>
                      </select>
                      <label for="blood_group"><i class="fa-solid fa-droplet"></i> Blood Group</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" placeholder="+880..." value="<?= e($old['emergency_contact'] ?? '') ?>">
                      <label for="emergency_contact"><i class="fa-solid fa-phone-volume"></i> Emergency Contact</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="text" class="form-control" id="occupation" name="occupation" placeholder="Engineer" value="<?= e($old['occupation'] ?? '') ?>">
                      <label for="occupation"><i class="fa-solid fa-briefcase"></i> Occupation</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <select class="form-select" id="marital_status" name="marital_status">
                        <option value="" <?= empty($old['marital_status']) ? 'selected' : '' ?>>Select status</option>
                        <option value="Single" <?= (($old['marital_status'] ?? '') === 'Single') ? 'selected' : '' ?>>Single</option>
                        <option value="Married" <?= (($old['marital_status'] ?? '') === 'Married') ? 'selected' : '' ?>>Married</option>
                        <option value="Divorced" <?= (($old['marital_status'] ?? '') === 'Divorced') ? 'selected' : '' ?>>Divorced</option>
                        <option value="Widowed" <?= (($old['marital_status'] ?? '') === 'Widowed') ? 'selected' : '' ?>>Widowed</option>
                      </select>
                      <label for="marital_status"><i class="fa-solid fa-ring"></i> Marital Status</label>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-floating">
                      <textarea class="form-control" id="address" name="address" placeholder="Address" style="min-height: 110px;"><?= e($old['address'] ?? '') ?></textarea>
                      <label for="address"><i class="fa-solid fa-location-dot"></i> Address</label>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="8" data-strong-password="true">
                      <label for="password"><i class="fa-solid fa-lock"></i> Password</label>
                      <div class="invalid-feedback">Use 8+ chars with uppercase, lowercase, number, and symbol.</div>
                    </div>
                    <div class="password-meter" aria-hidden="true">
                      <span></span>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-floating">
                      <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                      <label for="confirm_password"><i class="fa-solid fa-shield-halved"></i> Confirm Password</label>
                      <div class="invalid-feedback">Passwords must match.</div>
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="form-floating">
                      <select class="form-select" id="role" name="role" required>
                        <option value="" <?= empty($old['role']) ? 'selected' : '' ?>>Choose a role</option>
                        <?php foreach ($roles as $role): ?>
                          <option value="<?= e($role) ?>" <?= (($old['role'] ?? '') === $role) ? 'selected' : '' ?>><?= e($role) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <label for="role"><i class="fa-solid fa-user-shield"></i> User Role</label>
                      <div class="invalid-feedback">Please select a user role.</div>
                    </div>
                    <small class="text-muted">Hospital Admin and System Admin accounts are provisioned by the NHRE administration and cannot be self-registered.</small>
                  </div>
                </div>

                <button type="submit" class="btn btn-auth-primary ripple w-100 mt-4">
                  <span>Create Account</span>
                  <i class="fa-solid fa-user-plus"></i>
                </button>
              </form>

              <p class="auth-switch">Already registered? <a href="login.php">Login here</a></p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js?v=20260807-3"></script>
</body>
</html>
