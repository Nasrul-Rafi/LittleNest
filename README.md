# LittleNest

LittleNest is a child care service, booking, and child activity monitoring system developed for the CSE391 course.

The system provides separate portals for Parents, Caregivers, and Admin users. It covers the full process from child registration and service booking to caregiver assignment, activity monitoring, payment, refund, and reporting.

## Technologies Used

- Laravel 12
- PHP 8.2+
- MySQL
- Blade
- Tailwind CSS
- Vite
- Vanilla JavaScript
- SSLCOMMERZ Sandbox Payment Gateway
- XAMPP for local development

## User Roles

LittleNest has three main user roles.

### Parent

Parents can:

- Register and login
- Manage their profile
- Add, view, edit, and delete children
- Browse available child care services
- View available time slots
- Create bookings
- View booking details and booking history
- Search and filter bookings
- Cancel pending bookings
- Request cancellation for confirmed bookings
- Request booking rescheduling
- View assigned caregivers
- View child activity updates
- View activity details
- Download child activity summaries
- Pay for confirmed bookings through SSLCOMMERZ Sandbox
- View payment history
- View payment receipts
- Export payment history

### Caregiver

Caregivers can:

- Login using an account created by Admin
- Manage their professional profile
- View assigned children and bookings
- View their upcoming schedule
- Add child activity updates
- Edit their own activity updates
- View activity history
- Filter activity records
- Complete assigned care sessions

### Admin

Admin can:

- Manage Parent accounts
- Manage Children
- Manage Caregivers
- Manage Services
- Manage Time Slots
- Confirm or reject bookings
- Assign Caregivers to confirmed bookings
- Manage booking cancellation and reschedule requests
- Monitor child activities
- Manage payments
- Process eligible refunds
- Manage contact inquiries
- View dashboard summaries
- Search and filter operational records
- View service usage reports
- View caregiver workload reports
- View revenue and refund summaries
- Export booking reports as CSV
- Open print-ready reports for PDF saving

## Main Workflow

The main LittleNest workflow is:

1. A Parent registers or logs in.
2. The Parent creates a child profile.
3. The Parent chooses a child care service.
4. The Parent selects an available time slot.
5. A Pending booking is created.
6. Admin reviews the booking.
7. Admin confirms or rejects the booking.
8. Admin assigns an available Caregiver to a confirmed booking.
9. The Parent can make a payment through the SSLCOMMERZ Sandbox gateway.
10. The Caregiver views the assigned child and schedule.
11. The Caregiver records activity updates during the care session.
12. The Parent can view the assigned Caregiver and child activity updates.
13. The Caregiver completes the care assignment.
14. Booking, activity, payment, receipt, and report information remain available in the system.

## Booking and Time Slot System

Each time slot contains:

- Service
- Date
- Start time
- End time
- Capacity
- Status

The system checks the available capacity before creating a booking.

Pending and Confirmed bookings are counted when calculating occupied capacity.

Cancelled bookings do not keep a time slot occupied.

The system also prevents booking a closed or fully occupied time slot.

Each successful booking receives a unique LittleNest booking reference.

## Caregiver Assignment

Admin can assign an active and available Caregiver to a confirmed booking.

The assigned Caregiver can then view the child, booking details, and schedule.

Parents can also view the Caregiver assigned to their booking.

A Caregiver can only access assignments and activity records related to them.

## Child Activity Monitoring

Caregivers can record child care activities such as:

- Check-in
- Check-out
- Meal
- Nap
- Play
- Learning
- Toilet
- Health
- Medicine
- Mood
- Special Notes

Parents can view activity information related to their own children.

Admin can monitor activities and filter records by child, Caregiver, booking, activity type, and date.

## SSLCOMMERZ Sandbox Payment

LittleNest uses the SSLCOMMERZ Sandbox environment for payment gateway integration.

A Parent can start a payment only for an eligible confirmed booking.

The payment flow is:

1. LittleNest creates a payment request.
2. The Parent is redirected to the SSLCOMMERZ Sandbox checkout page.
3. The payment is completed using sandbox payment details.
4. SSLCOMMERZ redirects the user back to LittleNest.
5. LittleNest verifies the payment through the SSLCOMMERZ validation API.
6. The payment status is updated only after successful verification.

The system also supports:

- Successful payment handling
- Failed payment handling
- Cancelled payment handling
- Payment status checking
- Payment receipts
- Payment history
- SSLCOMMERZ refund requests
- Refund status checking

No real money is required while using the SSLCOMMERZ Sandbox environment.

## SSLCOMMERZ Configuration

SSLCOMMERZ credentials must be stored in the local or hosted `.env` file.

Example:

```env
SSLCOMMERZ_SANDBOX=true
SSLCOMMERZ_BASE_URL=https://sandbox.sslcommerz.com
SSLCOMMERZ_STORE_ID=
SSLCOMMERZ_STORE_PASSWORD=
```

Real Store ID and Store Password values must never be committed to GitHub.

The `.env.example` file contains only empty placeholders.

## Password Reset

LittleNest uses Laravel's password reset system.

For local development, if the mail driver is configured as `log`, password reset emails can be checked from:

```text
storage/logs/laravel.log
```

## Contact Inquiries

Visitors can send inquiries through the public Contact page.

Admin can view submitted inquiries and update their status.

## Reports

The Admin report section includes:

- Total bookings
- Completed bookings
- Service usage
- Caregiver workload
- Revenue summary
- Refund summary
- Activity statistics
- Date-based report filtering

Booking report data can be exported as CSV.

The printable report page can be saved as PDF using the browser's `Save as PDF` option.

## Security

LittleNest uses Laravel's built-in security features and backend access control.

The project includes:

- Password hashing
- CSRF protection
- Form validation
- Laravel authentication
- Role-based access control
- Ownership checks
- Eloquent database queries
- Payment validation
- Server-side booking amount verification

Parents can only access their own children, bookings, payments, and activity information.

Caregivers can only access assignments and activities related to them.

Admin-only pages verify the Admin role before allowing access.

Sensitive information such as database credentials, application keys, and SSLCOMMERZ credentials must remain inside the `.env` file.

The `.env` file must not be uploaded to GitHub.

## Local Setup

Place the project inside the XAMPP `htdocs` folder:

```text
C:\xampp\htdocs\LittleNest
```

Open Command Prompt and move to the project folder:

```bat
cd C:\xampp\htdocs\LittleNest
```

Install the required dependencies:

```bat
composer install
npm install
```

Create `.env` from `.env.example` if needed:

```bat
copy .env.example .env
```

Generate the Laravel application key if the project does not already have one:

```bat
php artisan key:generate
```

Create a MySQL database named:

```text
littlenest
```

Update the database configuration in `.env`.

Then run:

```bat
php artisan optimize:clear
php artisan migrate
php artisan storage:link
npm run build
```

To populate the project with demonstration data:

```bat
php artisan db:seed
```

Start the local development server:

```bat
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

## Demo Data

The project includes Laravel seeders for creating demonstration data for the main parts of the system.

The demo database includes data for:

- Admin
- Parents
- Parent profiles
- Caregivers
- Caregiver profiles
- Children
- Services
- Time slots
- Bookings
- Caregiver assignments
- Child activities
- Payments
- Booking requests
- Contact inquiries

Demo login credentials are intentionally not published in this repository.

## Testing

Run all automated tests using:

```bat
php artisan test
```

Useful commands before committing major changes:

```bat
php artisan optimize:clear
php artisan migrate:status
php artisan route:list
php artisan test
git status
```

## Deployment Notes

For a hosted environment:

- Use the correct hosted database credentials in `.env`
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set `APP_URL` to the public HTTPS website address
- Store SSLCOMMERZ credentials only in the hosted `.env`
- Run database migrations after uploading new migrations
- Clear Laravel configuration and application cache after changing `.env`

Example:

```bash
php artisan optimize:clear
php artisan migrate --force
```

The SSLCOMMERZ IPN endpoint should use a publicly accessible HTTPS URL when the application is deployed.

## Git Repository

```text
https://github.com/Nasrul-Rafi/LittleNest.git
```

## Project Note

LittleNest was developed as an academic project for CSE391.

The project focuses on child care service management, booking, caregiver assignment, child activity monitoring, payment gateway integration, refunds, inquiries, and administrative reporting.

SSLCOMMERZ Sandbox is used for development and demonstration purposes. Production payment credentials are not included in this repository.
