<?php /** Shared authenticated NHRE top navigation bar. The parent page must load auth_check.php first. */ ?>
<nav class="dashboard-nav">
  <div class="container d-flex align-items-center justify-content-between gap-3">
    <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
      <img src="assets/images/nhre-logo.svg" alt="NHRE" class="nhre-logo-img">
    </a>
    <div class="d-flex align-items-center gap-2">
      <a href="dashboard.php" class="btn btn-dashboard-logout ripple"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
      <a href="notifications.php" class="btn btn-dashboard-logout ripple"><i class="fa-solid fa-bell"></i> <span>Notifications</span></a>
      <a href="logout.php" class="btn btn-dashboard-logout ripple"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Logout</span></a>
    </div>
  </div>
</nav>
