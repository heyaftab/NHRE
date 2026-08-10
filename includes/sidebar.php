<?php
/** Shared authenticated NHRE sidebar. The parent page must load auth_check.php first. */
$sidebarRole = (string)($_SESSION['role'] ?? '');
$sidebarName = (string)($_SESSION['fullname'] ?? 'NHRE User');
$sidebarPage = basename((string)($_SERVER['PHP_SELF'] ?? ''));
$sidebarInitials = mb_strtoupper(mb_substr(trim($sidebarName), 0, 1));
$sidebarUnread = unread_notification_count((int)($_SESSION['user_id'] ?? 0));

$sidebarPhoto = '';
try {
    $sidebarPhotoStmt = db()->prepare('SELECT profile_photo FROM users WHERE id = ? LIMIT 1');
    $sidebarPhotoStmt->execute([(int)($_SESSION['user_id'] ?? 0)]);
    $sidebarPhoto = (string)$sidebarPhotoStmt->fetchColumn();
} catch (PDOException $e) {
}

$sidebarLinks = [
    ['dashboard.php', 'fa-house', 'Dashboard'],
    ['profile.php', 'fa-user', 'My Profile'],
];

if ($sidebarRole === 'Patient') {
    $sidebarLinks = array_merge($sidebarLinks, [
        ['coming_soon.php?feature=medical-records', 'fa-notes-medical', 'Medical Records', true],
        ['appointments.php', 'fa-calendar-check', 'Appointments'],
        ['coming_soon.php?feature=prescriptions', 'fa-prescription', 'Prescriptions', true],
        ['pharmacy.php', 'fa-pills', 'Pharmacy'],
        ['medical_tests.php', 'fa-flask-vial', 'Lab Reports'],
        ['vaccination.php', 'fa-syringe', 'Vaccinations'],
        ['coming_soon.php?feature=allergies', 'fa-triangle-exclamation', 'Allergies', true],
        ['blood_donation.php', 'fa-droplet', 'Blood Information'],
        ['coming_soon.php?feature=medical-documents', 'fa-folder-open', 'Medical Documents', true],
        ['data_access.php', 'fa-shield-halved', 'Data Access'],
    ]);
} elseif ($sidebarRole === 'Doctor') {
    $sidebarLinks = array_merge($sidebarLinks, [
        ['coming_soon.php?feature=patients', 'fa-user-group', 'My Patients', true],
        ['patient_search.php', 'fa-magnifying-glass', 'Patient Search'],
        ['appointments.php', 'fa-calendar-check', 'Appointments'],
        ['coming_soon.php?feature=medical-records', 'fa-notes-medical', 'Medical Records', true],
        ['coming_soon.php?feature=prescriptions', 'fa-pills', 'Prescriptions', true],
        ['medical_tests.php', 'fa-flask-vial', 'Lab Reports'],
        ['coming_soon.php?feature=medical-documents', 'fa-folder-open', 'Medical Documents', true],
        ['access_requests.php', 'fa-shield-halved', 'Access Requests'],
    ]);
} elseif ($sidebarRole === 'Lab Technician') {
    $sidebarLinks = array_merge($sidebarLinks, [
        ['medical_tests.php', 'fa-flask-vial', 'Test Requests'],
        ['coming_soon.php?feature=laboratory-reports', 'fa-file-medical', 'Laboratory Reports', true],
        ['patient_search.php', 'fa-magnifying-glass', 'Patient Search'],
        ['coming_soon.php?feature=test-history', 'fa-clock-rotate-left', 'Test History', true],
    ]);
} elseif ($sidebarRole === 'Pharmacist') {
    $sidebarLinks = array_merge($sidebarLinks, [
        ['pharmacy.php', 'fa-pills', 'Pharmacy'],
        ['coming_soon.php?feature=prescriptions', 'fa-prescription', 'Prescriptions', true],
        ['coming_soon.php?feature=inventory', 'fa-boxes-stacked', 'Medicine Inventory', true],
        ['coming_soon.php?feature=stock-management', 'fa-boxes-packing', 'Stock Management', true],
        ['patient_search.php', 'fa-magnifying-glass', 'Patient Search'],
        ['coming_soon.php?feature=dispensing-history', 'fa-clock-rotate-left', 'Dispensing History', true],
    ]);
} elseif ($sidebarRole === 'Hospital Admin') {
    $sidebarLinks = array_merge($sidebarLinks, [
        ['coming_soon.php?feature=hospital-profile', 'fa-hospital', 'Hospital Profile', true],
        ['coming_soon.php?feature=doctors', 'fa-user-doctor', 'Doctors', true],
        ['coming_soon.php?feature=patients', 'fa-user-group', 'Patients', true],
        ['coming_soon.php?feature=hospital-staff', 'fa-users', 'Hospital Staff', true],
        ['coming_soon.php?feature=departments', 'fa-table-cells-large', 'Departments', true],
        ['appointments.php', 'fa-calendar-check', 'Appointments'],
        ['coming_soon.php?feature=medical-records', 'fa-notes-medical', 'Medical Records', true],
        ['medical_tests.php', 'fa-flask-vial', 'Laboratory Services'],
        ['coming_soon.php?feature=prescriptions', 'fa-pills', 'Prescriptions', true],
        ['coming_soon.php?feature=access-requests', 'fa-shield-halved', 'Access Requests', true],
        ['admin_credentials.php', 'fa-users-gear', 'Account Directory'],
        ['coming_soon.php?feature=reports', 'fa-chart-column', 'Reports & Analytics', true],
        ['coming_soon.php?feature=audit-logs', 'fa-clipboard-list', 'Audit Logs', true],
    ]);
} elseif ($sidebarRole === 'System Admin') {
    $sidebarLinks = array_merge($sidebarLinks, [
        ['coming_soon.php?feature=user-management', 'fa-users-gear', 'User Management', true],
        ['coming_soon.php?feature=organizations', 'fa-building', 'Healthcare Organizations', true],
        ['coming_soon.php?feature=medical-records', 'fa-notes-medical', 'Medical Records', true],
        ['appointments.php', 'fa-calendar-check', 'Appointments'],
        ['coming_soon.php?feature=prescriptions', 'fa-pills', 'Prescriptions', true],
        ['medical_tests.php', 'fa-flask-vial', 'Laboratory Reports'],
        ['coming_soon.php?feature=access-overview', 'fa-shield-halved', 'Access Permissions', true],
        ['coming_soon.php?feature=audit-logs', 'fa-clipboard-list', 'Audit Logs', true],
        ['coming_soon.php?feature=reports', 'fa-chart-column', 'Reports & Analytics', true],
        ['coming_soon.php?feature=system-statistics', 'fa-chart-pie', 'System Statistics', true],
        ['coming_soon.php?feature=settings', 'fa-sliders', 'System Settings', true],
    ]);
}

$sidebarLinks[] = ['notifications.php', 'fa-bell', 'Notifications'];
$sidebarLinks[] = ['coming_soon.php?feature=settings', 'fa-gear', 'Settings', true];
$sidebarLinks[] = ['help_support.php', 'fa-circle-question', 'Help & Support'];
?>
<button class="sidebar-toggle" type="button" aria-label="Open navigation" aria-controls="nhreSidebar" aria-expanded="false">
  <i class="fa-solid fa-bars"></i>
</button>
<div class="sidebar-backdrop" hidden></div>
<aside class="nhre-sidebar" id="nhreSidebar" aria-label="NHRE account navigation">
  <div class="sidebar-brand">
    <img src="assets/images/nhre-logo.svg" alt="NHRE" class="nhre-logo-img">
    <button class="sidebar-collapse" type="button" aria-label="Collapse navigation"><i class="fa-solid fa-angles-left"></i></button>
  </div>
  <p class="sidebar-tagline">National Healthcare<br>Record Exchange</p>
  <a class="sidebar-user" href="profile.php" title="View profile">
    <span class="sidebar-avatar" aria-hidden="true">
      <?php if ($sidebarPhoto !== ''): ?>
        <img src="<?= e($sidebarPhoto) ?>" alt="">
      <?php else: ?>
        <?= e($sidebarInitials) ?>
      <?php endif; ?>
    </span>
    <span class="sidebar-user-text"><strong><?= e($sidebarName) ?></strong><small><?= e($sidebarRole) ?></small></span>
  </a>
  <nav class="sidebar-menu">
    <span class="sidebar-label">Workspace</span>
    <?php foreach ($sidebarLinks as $sidebarLink): ?>
      <?php
      [$sidebarHref, $sidebarIcon, $sidebarLabel] = $sidebarLink;
      $sidebarPlaceholder = $sidebarLink[3] ?? false;

      $sidebarPath = basename((string)parse_url($sidebarHref, PHP_URL_PATH) ?: $sidebarHref);
      $sidebarActive = $sidebarPage === $sidebarPath;
      if ($sidebarActive && $sidebarPath === 'coming_soon.php') {
          parse_str((string)parse_url($sidebarHref, PHP_URL_QUERY), $sidebarParams);
          $sidebarActive = ($_GET['feature'] ?? '') === ($sidebarParams['feature'] ?? '');
      }
      ?>
      <a href="<?= e($sidebarHref) ?>" class="sidebar-link <?= $sidebarActive ? 'is-active' : '' ?>" title="<?= e($sidebarLabel) ?>">
        <i class="fa-solid <?= e($sidebarIcon) ?>"></i>
        <span><?= e($sidebarLabel) ?></span>
        <?php if ($sidebarPlaceholder): ?><em>Planned</em><?php endif; ?>
        <?php if ($sidebarLabel === 'Notifications' && $sidebarUnread > 0): ?><em class="sidebar-unread"><?= $sidebarUnread > 99 ? '99+' : $sidebarUnread ?></em><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <a class="sidebar-logout" href="logout.php" title="Logout"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Logout</span></a>
</aside>
