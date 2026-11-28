# Visitor Authentication System

A Laravel web application that replaces the paper visitor register. Visitors register online, confirm their email
with a one-time password (OTP), get approved by the person they are visiting, and receive a QR pass that security
scans at the gate. Every entry and exit is logged, and administrators can search and export the records.

## Features

- **Role-based staff login:** Admin, Security and Host, with login throttling, password reset and account deactivation.
- **User management:** admin can create, view, edit, delete and activate/deactivate staff accounts. Accounts with visit history cannot be deleted, only deactivated.
- **Visitor registration:** public form (photo, ID proof, host, purpose, date, time) and a walk-in form for security staff.
- **OTP verification:** 6-digit code, stored hashed, valid for 5 minutes, locked after 3 wrong attempts, with resend.
- **Host approval:** approve or reject with remarks; email notifications to host and visitor.
- **QR pass:** unique 64-character token shown as a QR code; security validates status, date and blacklist.
- **Check-in / check-out:** timestamps and the staff member are recorded in a visit log; camera scanning supported.
- **Blacklist:** admin can block and unblock visitors; blocked visitors cannot register or enter.
- **Dashboard and reports:** role-wise dashboard, filterable visit log, CSV and PDF export.
- **Automatic expiry:** a scheduled command expires passes that were not used on the visit date.

## Built with

Laravel 11 (PHP 8.2+), MySQL, Blade, Bootstrap 5, simple-qrcode, laravel-dompdf, html5-qrcode.

## Installation (Laragon on Windows)

1. Start Laragon, then **Quick app > Laravel** and name it `visitor-auth`.
2. Copy this repository's files into `C:\laragon\www\visitor-auth` (replace when asked).
3. In the Laragon terminal:
   ```
   cd C:\laragon\www\visitor-auth
   composer require simplesoftwareio/simple-qrcode barryvdh/laravel-dompdf
   ```
4. Set up the database, using **either** option:
   - **Option A (migrations):** create an empty database `visitor_auth_db`, then run
     ```
     php artisan migrate --seed
     ```
   - **Option B (SQL file):** create an empty database `visitor_auth_db` and import `visitor_auth_db.sql`
     with HeidiSQL or phpMyAdmin. Do not run `migrate` afterwards.
5. Edit `.env` (see `env-snippet.txt`):
   ```
   APP_NAME="Visitor Authentication"
   DB_CONNECTION=mysql
   DB_DATABASE=visitor_auth_db
   DB_USERNAME=root
   DB_PASSWORD=
   MAIL_MAILER=log
   ```
6. Run:
   ```
   php artisan key:generate
   php artisan storage:link
   php artisan serve
   ```
   Then open `http://visitor-auth.test` (Laragon) or `http://127.0.0.1:8000`.

## Default staff accounts

Password for all accounts is `password`. Change it before real use.

| Role     | Email                |
|----------|----------------------|
| Admin    | admin@example.com    |
| Security | security@example.com |
| Host     | host@example.com     |
| Host     | host2@example.com    |

## How to try the full flow

1. Open `/visitor/register` and submit the visitor form.
2. With `MAIL_MAILER=log`, emails are written to `storage/logs/laravel.log`. Copy the 6-digit OTP from there.
3. Log in as the chosen host, open **Visit requests** and approve. The visitor's page then shows the QR pass.
4. Log in as Security, open **Check-in / out**, and scan or paste the pass code. Check the visitor out later.
5. Log in as Admin to manage users, the blacklist and the reports.

## Scheduler (expires unused passes)

```
php artisan schedule:work      # development
php artisan visits:expire      # run once by hand
```

## Tests

```
php artisan test
```
`tests/Feature/VisitorAuthenticationTest.php` covers login and roles, registration, OTP, approval, check-in/out,
expiry, reports and encryption of the ID number.

## Project structure

| Module | Main files |
|---|---|
| Authentication and roles | `Auth/LoginController`, `Auth/PasswordController`, `Middleware/RoleMiddleware` |
| User management | `Admin/UserController` |
| Registration | `VisitorPortalController`, `Security/WalkinController`, `Services/VisitRegistrationService` |
| OTP | `Services/OtpService` |
| Host approval | `Host/RequestController` |
| QR pass, check-in/out | `Security/CheckinController`, `Console/Commands/ExpireVisits` |
| Blacklist | `Admin/BlacklistController` |
| Dashboard and reports | `DashboardController`, `ReportController` |

Database tables: `users`, `visitors`, `visits`, `visit_logs`, `otp_verifications` (plus Laravel's own tables).
