# LittleNest

LittleNest is a child care service, booking, and child activity monitoring system developed for the CSE391 course.

The system is built with Laravel and provides separate features for Parents, Caregivers, and Admin users.

## Technologies Used

- Laravel 12
- PHP 8.2+
- MySQL
- Blade
- Tailwind CSS
- Vite
- Vanilla JavaScript
- XAMPP for local development

## User Roles

### Parent

Parents can:

- Register and login
- Manage their profile
- Add and manage children
- Browse child care services
- View available time slots
- Create bookings
- View and filter booking history
- Request booking cancellation or rescheduling
- View assigned caregivers
- Follow child activity updates
- View activity details
- Export child activity summaries
- Pay confirmed bookings through the SSLCOMMERZ sandbox gateway
- View receipts and payment history
- Export payment history

### Caregiver

Caregivers can:

- Login using accounts created by Admin
- Manage their professional profile
- View assigned children and bookings
- View their care schedule
- Add child activity updates
- Edit their own activity updates
- View activity history
- Complete assigned care sessions

### Admin

Admin can:

- Manage Parents
- Manage Children
- Manage Caregivers
- Manage Services
- Manage Time Slots
- Manage Bookings
- Confirm or reject bookings
- Assign Caregivers to bookings
- Manage cancellation and reschedule requests
- Monitor child activities
- Manage payments and refunds
- Manage contact inquiries
- View and filter reports
- Export booking reports as CSV
- View service usage and caregiver workload
- View revenue and refund summaries
- Open print-ready reports for PDF saving

## Main Workflow

The main workflow of LittleNest is:

1. A Parent registers or logs in.
2. The Parent adds a child profile.
3. The Parent selects a child care service.
4. The Parent chooses an available time slot.
5. A Pending booking is created.
6. Admin reviews the booking.
7. Admin confirms or rejects the booking.
8. Admin assigns an available Caregiver to a confirmed booking.
9. The Caregiver views the assigned child and schedule.
10. The Caregiver records activity updates during the care session.
11. The Parent can view the assigned Caregiver and activity information.
12. The Caregiver completes the care assignment.
13. Booking, activity, and payment history remain available in the system.

## Local Setup

Place the project inside the XAMPP `htdocs` folder:

```text
C:\xampp\htdocs\LittleNest
```

Open Command Prompt and go to the project folder:

```bat
cd C:\xampp\htdocs\LittleNest
```

Install the required dependencies:

```bat
composer install
npm install
```

Create the `.env` file from `.env.example` if needed:

```bat
copy .env.example .env
```

Generate the Laravel application key:

```bat
php artisan key:generate
```

Create a MySQL database named:

```text
littlenest
```

Update the database settings in the `.env` file according to your local MySQL configuration.

Then run:

```bat
php artisan optimize:clear
php artisan migrate
php artisan storage:link
npm run build
```

Start the Laravel development server:

```bat
php artisan serve
```

Open the project in a browser:

```text
http://127.0.0.1:8000
```

## Demo Data

LittleNest includes Laravel seeders for creating demonstration data.

Run:

```bat
php artisan db:seed
```

The seeders create data for the main parts of the system, including:

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

This data is intended for local development, testing, project demonstration, and viva purposes.

## Default Admin Account

After running:

```bat
php artisan db:seed
```

the demo Admin account can be used to access the Admin panel.

```text
Email: admin@littlenest.test
Password: 12345678
```

This account is intended only for local development and project demonstration.

Do not use these credentials for a real production deployment.

## Password Reset

LittleNest uses Laravel's password reset system.

For local development, if the mail driver is configured as `log`, password reset emails can be checked from:

```text
storage/logs/laravel.log
```

## Booking and Time Slot System

Parents select an available time slot while creating a booking.

Each time slot contains information such as:

- Service
- Date
- Start time
- End time
- Capacity
- Status

The system checks the available capacity before allowing a booking.

Pending and Confirmed bookings are considered when calculating occupied capacity, while Cancelled bookings do not keep a slot occupied.

## Caregiver Assignment

Admin can assign an available Caregiver to a confirmed booking.

The assigned Caregiver can view the child, booking information, and schedule.

Parents can also view the Caregiver assigned to their booking.

## Child Activity Monitoring

Caregivers can record activity updates during a child care session.

Activity records may include information such as:

- Check-in
- Meals
- Learning activities
- Play activities
- Rest or nap
- Health updates
- Medicine information
- Behaviour or mood
- Other care notes

Parents can view activity information related to their own children.

Admin can also monitor child activities using the available filters.

## Payments and Refunds

LittleNest uses the SSLCOMMERZ sandbox gateway for payment testing.

Add the sandbox Store ID and Store Password to the local `.env` file:

```env
SSLCOMMERZ_SANDBOX=true
SSLCOMMERZ_BASE_URL=https://sandbox.sslcommerz.com
SSLCOMMERZ_STORE_ID=your_store_id
SSLCOMMERZ_STORE_PASSWORD=your_store_password
```

After changing `.env`, run:

```bat
php artisan optimize:clear
```

Parents can start payment only for Confirmed bookings. LittleNest creates a Pending payment, sends the booking amount to SSLCOMMERZ, redirects the Parent to the hosted sandbox checkout page, and validates the returned transaction before marking the payment as Paid.

Payment records can include Pending, Paid, and Failed states. Paid payments can generate receipts. For SSLCOMMERZ payments, eligible cancelled bookings can send a refund request to the sandbox gateway and the Admin can check the refund status from the payment details page.

SSLCOMMERZ credentials must stay in `.env` and must not be committed to GitHub.

## Booking Requests

Parents can submit:

- Cancellation requests
- Reschedule requests

Admin can review these requests and either approve or reject them.

Request history and Admin review information remain available in the system.

## Reports

The Admin report section provides information such as:

- Booking statistics
- Service usage
- Caregiver workload
- Revenue summary
- Refund summary
- Date-based report filtering

Booking report data can be exported as CSV.

The PDF report option opens a print-ready page, which can be saved using the browser's `Save as PDF` option.

## Contact Inquiries

Visitors can send inquiries through the public contact page.

Admin can view and manage submitted inquiries from the Admin panel.

## Security

LittleNest uses Laravel's built-in security features and backend access control.

The project includes:

- Password hashing
- CSRF protection
- Form validation
- Laravel authentication
- Eloquent database queries
- Role-based access control
- Ownership checks

Parents can only access their own children, bookings, payments, and activity information.

Caregivers can only access assignments and activity information related to them.

Admin-only pages verify the Admin role before allowing access.

Sensitive local configuration such as database credentials should be stored in the `.env` file.

The `.env` file should not be uploaded to GitHub.

## Testing

Run the project tests using:

```bat
php artisan test
```

Before committing major changes, the following commands can also be used:

```bat
php artisan optimize:clear
php artisan migrate:status
php artisan route:list
php artisan test
git status
```

## Git Repository

```text
https://github.com/Nasrul-Rafi/LittleNest.git
```

## Project Note

LittleNest was developed as an academic project for CSE391.

The current system focuses on child care service management, booking, caregiver assignment, child activity monitoring, payments, and administrative management.

The payment process used in this project is simulated for demonstration purposes.
