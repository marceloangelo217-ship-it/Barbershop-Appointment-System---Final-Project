# Sharp Edge Barbershop — Laravel Appointment System

Final course output for **Data Structure, Algorithm and Analysis**. This is a Laravel web application that uses **JSON file handling** for persistent storage (no MySQL/SQLite database for business records).

## Objectives
- Provide customers a simple way to book a barbershop appointment.
- Prevent conflicting bookings for the same barber, date, and time slot.
- Give administrators a protected area to review and manage booking statuses.

## Intended Users
- **Customer** — registers, books a visit, views own bookings, cancels eligible pending bookings, tracks a booking by reference.
- **Administrator** — logs in, views/searches/filters bookings, updates booking status.

## Scope
- Services, barbers, and time slots are defined in `app/Services/BookingData.php`.
- Records are stored in private JSON files: `storage/app/private/users.json` and `storage/app/private/bookings.json`.
- Sessions use file driver; no database is required.

## Features
- Customer registration and login (hashed passwords via Laravel Hash).
- Booking with unique reference number (`BB-YYYYMMDD-0001`).
- Conflict prevention: same barber/date/slot cannot be double-booked; user cannot book the same active slot twice. Availability is rechecked inside a file lock before saving.
- Track booking by reference number.
- My bookings with cancel for Pending bookings.
- Admin dashboard with search/filter and status transitions.
- CSRF protection on all POST forms, server-side validation, escaped Blade output.

## Workflow
1. Customer registers or logs in.
2. Customer selects service, barber, and date; available slots are shown.
3. Customer confirms booking and receives a reference number.
4. Admin reviews booking and updates status (Pending → Approved/Rejected/Cancelled, Approved → Completed/Cancelled).
5. Customer can track status by reference or view it in My Bookings.

## Limitations
- JSON flat-file storage is suitable for class/demo scale, not high-concurrency production.
- No email/SMS notifications.
- No password reset/email verification.
- Schedule, services, and barbers are configured in code.

## Project Structure
- `routes/web.php` — URL routes.
- `app/Http/Controllers/` — request handling.
- `app/Services/JsonStorageService.php` — centralized JSON read/write with file locking.
- `app/Services/BookingData.php` — services, barbers, slots, rules.
- `resources/views/` — Blade templates.
- `public/css`, `public/images` — direct public assets loaded with `asset()` (Vite still runs as part of `composer run dev`, but our pages do not require `@vite`).
- `storage/app/private/` — private JSON records (live data, not committed).
- `docs/sample-data/` — fictional sample JSON files for submission.

## Setup (Local / XAMPP)
1. Install PHP 8.3+ (8.5 used here), Composer, Node.js/npm, and Git.
2. Copy `.env.example` to `.env`.
3. Install dependencies and build assets **once**:
   ```powershell
   composer install
   php artisan key:generate
   npm install
   npm run build
   ```
4. Start the development environment:
   ```powershell
   composer run dev
   ```
   This starts three processes together: `php artisan serve` (server),
   `php artisan queue:listen` (queue), and `npm run dev` (Vite).
5. Open `http://localhost:8000`. Press `Ctrl + C` to stop.

If port 8000 is busy, run `php artisan serve --port=8001` instead.
To reinstall Laravel is not needed each session — just `composer run dev` again.

## Default Demo Credentials
- Admin: `admin@barbershop.test` / `Admin@12345`
- Customer: `juan@example.com` / `Customer@123`

## Testing Checklist
| Test | Expected | Actual |
|---|---|---|
| Register valid customer | Account created, redirected to booking |  |
| Login invalid password | Error message shown |  |
| Book available slot | Reference generated, booking saved in JSON |  |
| Book same barber/date/slot | Rejected with conflict message |  |
| Track valid reference | Booking details and status shown |  |
| Admin updates Pending to Approved | Status changes and persists after reload |  |
| Cancel Pending booking as owner | Status becomes Cancelled |  |
| Missing/corrupt JSON | App handles gracefully / returns empty or error |  |

## Security Notes
- Admin routes protected by `admin` middleware.
- All modifying forms include `@csrf`.
- Passwords hashed with `Hash::make`.
- User input validated server-side and rendered with Blade escaping `{{ }}`.
- JSON files stored in `storage/app/private` (not public).
- Set `APP_DEBUG=false` in production.
