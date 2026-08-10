<?php
require_once __DIR__ . '/auth/auth_check.php';
redirect_if_authenticated();

$errors = session_pull('errors', []);
$old = session_pull('old', []);
$success = session_pull('success');
$error = session_pull('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - NHRE</title>
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
        <div class="col-lg-5 d-none d-lg-block">
          <div class="auth-info-panel">
            <div class="auth-info-icon"><i class="fa-solid fa-shield-heart"></i></div>
            <h1>Secure access to national health records.</h1>
            <p>Authenticate safely to view records, appointments, and role-based healthcare tools.</p>
            <div class="auth-info-list">
              <span><i class="fa-solid fa-lock"></i> Session protected</span>
              <span><i class="fa-solid fa-database"></i> Encrypted credentials</span>
              <span><i class="fa-solid fa-user-shield"></i> Role-based dashboard</span>
            </div>
          </div>
        </div>

        <div class="col-lg-5 col-md-8">
          <div class="card auth-card glass-card">
            <div class="card-body">
              <div class="auth-card-head">
                <span class="auth-kicker">Welcome back</span>
                <h2>Login to NHRE</h2>
                <p>Use your registered email and password to continue.</p>
              </div>

              <?php if ($success): ?>
                <div class="alert alert-success auth-alert" role="alert">
                  <i class="fa-solid fa-circle-check"></i>
                  <span><?= e($success) ?></span>
                </div>
              <?php endif; ?>

              <?php if ($error): ?>
                <div class="alert alert-warning auth-alert" role="alert">
                  <i class="fa-solid fa-triangle-exclamation"></i>
                  <span><?= e($error) ?></span>
                </div>
              <?php endif; ?>

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

              <form action="auth/login_process.php" method="POST" class="needs-validation auth-form" id="loginForm" novalidate>
                <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                <div class="form-floating mb-3">
                  <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?= e($old['email'] ?? '') ?>" required>
                  <label for="email"><i class="fa-solid fa-envelope"></i> Email</label>
                  <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>

                <div class="form-floating mb-3">
                  <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                  <label for="password"><i class="fa-solid fa-key"></i> Password</label>
                  <div class="invalid-feedback">Please enter your password.</div>
                </div>

                <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">Remember Me</label>
                  </div>
                  <a href="forgot_password.php" class="auth-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-auth-primary ripple w-100">
                  <span>Login</span>
                  <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
              </form>

              <p class="auth-switch">New to NHRE? <a href="register.php">Create an account</a></p>
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
