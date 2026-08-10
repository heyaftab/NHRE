# NHRE — National Healthcare Record Exchange

NHRE is a PHP and MySQL healthcare portal prototype. It provides a public landing page and an authenticated workspace for patients and healthcare roles, with account management, appointments, medical-test bookings, notifications, vaccination/report viewing, pharmacy information, and blood donation workflows.

> This repository is a demonstration project, not a production-ready electronic health record system. The claims and statistics on the landing page are presentation content, and the application should not be used for real patient data without a full security, privacy, and regulatory review.

## Features

- Registration for Patient, Doctor, Pharmacist, and Lab Technician roles (Hospital Admin and System Admin are provisioned by administrators and cannot be self-registered)
- Login sessions, CSRF protection, password hashing, failed-login throttling, and optional remember-me tokens
- Profile management with optional profile-photo uploads (maximum 2 MB)
- In-app notifications with unread counts and a mark-all-read action
- Shared responsive, collapsible sidebar navigation with role-specific links and unread-notification access
- Patient-controlled data access: grant/revoke record access to doctors and hospitals, pending access requests, and an access history audit log
- Doctor patient search (by name, NID, phone, email) with consent-gated access to authorized records
- Appointment scheduling: patients can find doctors and book or cancel appointments; doctors can manage assigned appointments; Hospital Admins can oversee them
- Medical-test marketplace with filtering, bookings, lab-technician status updates, and result-file uploads
- Vaccination schedule interface and patient-specific doctor-report viewing
- Pharmacy medicine catalogue and pharmacy-support request workflow
- Blood donor registration, blood requests, donor directory, and donor eligibility tracking
- Password reset links generated in the application for local development
- Responsive Bootstrap interface with custom CSS and JavaScript

## Technology

- PHP 8.1+ (PDO and `mbstring`)
- MySQL 5.7+ or MariaDB 10.4+
- HTML5, CSS, and vanilla JavaScript
- Bootstrap 5.3.3, Font Awesome 6.5.1, and Google Fonts loaded from CDNs
- Apache/XAMPP (recommended for the supplied setup), or PHP's built-in development server

No Composer or npm installation is required.

## Project structure

```text
NHRE/
├── assets/
│   ├── css/styles.css            # Shared application styles
│   └── js/app.js                 # UI behavior and notification polling
├── auth/                         # Form handlers and authenticated JSON endpoint
├── config/
│   └── database.php              # Database, session, and login settings
├── includes/
│   └── sidebar.php               # Shared authenticated role-based navigation
├── database/
│   └── nhre.sql                  # Database schema and optional demo user
├── uploads/profile_pics/         # Created automatically after a photo upload
├── uploads/test_results/         # Lab-result files; created automatically when needed
├── index.php                     # Public landing page
├── dashboard.php                 # Authenticated home
├── profile.php                   # Profile view and editor
├── notifications.php             # Notification list
├── appointments.php              # Doctor discovery and appointment management
├── medical_tests.php             # Medical-test catalogue and bookings
├── vaccination.php               # Vaccination/doctor reports
├── pharmacy.php                  # Pharmacy module
├── data_access.php               # Patient-controlled record access and history
├── patient_search.php            # Doctor/Lab/Pharmacy patient lookup
├── authorized_records.php        # Consent-gated patient record view
├── access_requests.php           # Doctor access-request tracking
├── blood_donation.php            # Donor and blood-request workflows
├── admin_credentials.php         # Hospital Admin account credential view
├── coming_soon.php               # Authorized placeholders for planned workspaces
├── help_support.php               # Authenticated help and privacy guidance
└── test.php                      # Local setup diagnostic
```

## Local setup with XAMPP

1. Start **Apache** and **MySQL** in XAMPP.
2. Place the project in XAMPP's document root. The expected macOS location is:

   ```text
   /Applications/XAMPP/xamppfiles/htdocs/NHRE
   ```

3. Import [`database/nhre.sql`](database/nhre.sql) using phpMyAdmin, or from a terminal:

   ```bash
   /Applications/XAMPP/xamppfiles/bin/mysql -u root < database/nhre.sql
   ```

   The application can also create the appointment and medical-test tables at runtime when those modules are first opened. Importing the schema remains the recommended setup path because it creates the complete database structure up front.

4. Confirm the connection values in [`config/database.php`](config/database.php). The included defaults are:

   ```php
   DB_HOST = localhost
   DB_NAME = nhre
   DB_USER = root
   DB_PASS = (empty)
   ```

5. Open [http://localhost/NHRE/](http://localhost/NHRE/) in a browser.
6. Optionally visit [http://localhost/NHRE/test.php](http://localhost/NHRE/test.php) to check the PHP version, PDO MySQL connection, database tables, writable session directory, and configured roles. Remove or restrict this diagnostic page before deployment.

### PHP built-in server

If MySQL is already running and configured, the application can also be served from the project directory:

```bash
php -S localhost:8000
```

Then visit [http://localhost:8000](http://localhost:8000).

## First account

The normal route is to create an account at `register.php`. Passwords must contain at least eight characters, including uppercase, lowercase, numeric, and symbol characters.

The SQL file also contains commented demo Hospital Admin and System Admin inserts. Uncomment them before importing (or run them separately) to enable:

- Hospital Admin: `admin@nhre.gov` / `Admin123!`
- System Admin: `sysadmin@nhre.gov` / `SysAdmin123!`

Do not retain demo credentials in any deployed environment.

## Configuration

Runtime constants are defined in [`config/database.php`](config/database.php):

| Setting | Default | Purpose |
| --- | --- | --- |
| `SESSION_NAME` | `nhre_session` | Session cookie name |
| `SESSION_LIFETIME` | `7200` | Session cookie lifetime in seconds |
| `COOKIE_REMEMBER_DAYS` | `30` | Remember-me token lifetime |
| `LOGIN_ATTEMPT_LIMIT` | `5` | Failed attempts allowed per email/IP window |
| `LOGIN_ATTEMPT_WINDOW_MINUTES` | `15` | Login throttling window |

For shared or production-like environments, use a dedicated database account and keep credentials outside the web root rather than committing them to source control.

## Password reset behavior

This prototype does not send email. After a valid email is submitted at `forgot_password.php`, the generated one-hour reset URL is displayed directly in the browser. Connect the handler to a trusted mail provider and stop exposing the URL in the response before production use.

## Data and role notes

- The schema creates user, authentication, doctor-directory, blood-donation, notification, doctor-report, appointment, medical-test, and medical-test-booking tables. This includes `users`, `districts`, `specializations`, `hospitals`, `appointments`, `medical_tests`, and `medical_test_bookings`. Patient data-access tables (`access_permissions`, `access_logs`) and `pharmacy_requests` are also created.
- The application recognizes six roles: Patient, Doctor, Pharmacist, Lab Technician, Hospital Admin, and System Admin. Self-registration supports Patient, Doctor, Pharmacist, and Lab Technician only; Hospital Admin and System Admin accounts are provisioned by an administrator (seed inserts in `database/nhre.sql`).
- The authenticated sidebar is role-aware and only links to a role's existing workspaces or clearly labeled planned workspaces. Planned workspaces enforce their allowed roles on the server and do not expose patient records.
- Patient data access: patients grant or revoke record access (Medical History, Lab Reports, Prescriptions, Vaccinations, Allergies, Medical Documents) to individual doctors or to hospitals. Doctors can request access, which appears as a pending request for the patient to approve or reject. `authorized_records.php` enforces an active, unexpired permission server-side and writes to the access log.
- Hospital-only donor controls and appointment administration are shown conditionally in the interface; server-side role authorization should be strengthened before deployment.
- The pharmacy catalogue is interface/demo data, but the pharmacy support-request form writes real rows to `pharmacy_requests`.
- Vaccination schedules are currently static reference content. Doctor reports and notifications require records to be inserted into their respective database tables; there is no administrative creation interface for doctor reports yet.
- Medical-test catalogue entries are seeded with demo data if the catalogue is empty. Lab result files are stored under `uploads/test_results/` and should be treated as untrusted uploads.

## Security checklist before deployment

- Serve the site only over HTTPS and use secure environment-specific database credentials.
- Add strict server-side authorization checks to every privileged action.
- Validate profile photos and laboratory-result files from file contents, store uploads outside the public web root, and disable script execution in upload directories.
- Replace the on-screen password-reset link with email delivery.
- Add audit logging, consent enforcement, data encryption, retention rules, backups, and regulatory review.
- Remove `test.php`, disable PHP error display, and add appropriate security headers.
- Review cleanup/expiry handling for login attempts, remember-me tokens, and reset tokens.

## Basic verification

Lint every PHP file:

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

For a manual smoke test, import the schema, register a user, log in, edit the profile, book and manage an appointment using the relevant roles, create a medical-test booking, register as a donor, submit a blood request, and test logout and password reset. For the data-access flow: log in as a patient, grant a doctor access and confirm the permission appears; log in as that doctor, search for the patient, open the authorized records, and confirm the access log records the view.

## License

No license file is currently included. Unless the project owner adds one, all rights are reserved by default.
