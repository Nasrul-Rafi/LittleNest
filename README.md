# LittleNest

LittleNest is a Laravel-based child care service, booking and child activity monitoring system developed for CSE391.

## Technology

- Laravel 12
- PHP 8.2+
- MySQL
- Blade
- Tailwind CSS and Vite
- Vanilla JavaScript where needed
- XAMPP for local development

## Roles

### Parent

Parents can register, manage their profile and children, browse services, select available time slots, create bookings, filter booking history, request reschedule or cancellation, view assigned caregivers, follow child activities, download activity summaries, make simulated payments, view receipts and export payment history.

### Caregiver

Caregivers can login using accounts created by Admin, manage their professional profile, see assigned children and schedules, add or edit care activity updates, review activity history and complete confirmed care assignments.

### Admin

Admin can manage parents, children, caregivers, services, time slots, bookings, caregiver assignments, booking requests, filtered activity monitoring, payments, refunds, inquiries and reports.

## Main Workflow

Parent registers or logs in.

Parent adds a child.

Parent chooses a service and an available time slot.

A Pending booking is created.

Admin confirms or rejects the booking.

Admin assigns an available caregiver.

Caregiver records activity updates.

Parent sees caregiver and activity information.

Caregiver completes the care assignment.

Payment and booking history remain available.

## Local Setup

Place the project in:

```text
C:\xampp\htdocs\LittleNest
```

Open Command Prompt:

```bat
cd C:\xampp\htdocs\LittleNest
composer install
npm install
```

Create `.env` from `.env.example` if needed:

```bat
copy .env.example .env
php artisan key:generate
```

Create a MySQL database named:

```text
littlenest
```

Update the MySQL settings in `.env`.

Then run:

```bat
php artisan optimize:clear
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

If you want the default Admin account and the six demo services, run this once:

```bat
php artisan db:seed
```

The seeders use `updateOrCreate`, so running them again updates the same Admin and service records instead of creating duplicates.

Open:

```text
http://127.0.0.1:8000
```

## Default Admin Account

After running `php artisan db:seed`:

```text
Email: admin@littlenest.test
Password: password123
```

Change the password if the project is used beyond local demonstration.

## Password Reset

Password reset uses Laravel's password broker.

For local development, if the mail driver is set to `log`, reset emails are written to:

```text
storage/logs/laravel.log
```

## Reports

Admin reports support date filtering, booking CSV export, service usage, caregiver workload, revenue and refund summaries.

The Export PDF button opens a print-ready report. Choose `Save as PDF` from the browser print dialog.

## Testing

Run:

```bat
php artisan test
```

Before a Git commit, also run:

```bat
php artisan optimize:clear
php artisan migrate:status
php artisan route:list
php artisan test
git status
```

## Security

LittleNest uses Laravel authentication, CSRF protection, password hashing, validation, Eloquent parameter binding and backend role or ownership checks.

Parents can only access their own records.

Caregivers can only access assignments and activities related to them.

Admin-only pages verify the Admin role in backend controller logic.

## Git Repository

```text
https://github.com/Nasrul-Rafi/LittleNest.git
```
