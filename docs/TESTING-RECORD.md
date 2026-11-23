# Testing Record

Environment: PHP 8.5.12 (XAMPP), Laravel 13.11, local server `php artisan serve`, Chrome browser.

| # | Test case | Expected result | Actual result |
|---|---|---|---|
| 1 | Home page loads | Hero, gallery, services, barbers, address display | PASS — all sections displayed |
| 2 | Login with invalid password | "Invalid email or password." | PASS |
| 3 | Customer login | Redirect to booking page | PASS |
| 4 | Select service/barber/date | Available slots shown | PASS — 9 slots displayed |
| 5 | Book an available slot | Unique reference generated; record saved to JSON | PASS — `BB-20261125-0001` saved |
| 6 | Double book same barber/date/slot | Rejected with conflict message | PASS — checked inside file lock |
| 7 | Track valid reference | Details and status card shown | PASS |
| 8 | Track unknown reference | "No booking found..." | PASS |
| 9 | Admin login | Redirect to Admin dashboard | PASS |
| 10 | Admin status change Pending → Approved | Status changes and persists after reload | PASS |
| 11 | Customer cancel Pending booking | Status becomes Cancelled | PASS |
| 12 | Delete/corrupt JSON file | App handles gracefully (empty data or error message) | PASS — `JsonStorageService::read()` returns [] on missing/invalid JSON; `mutate()` throws controlled error |
| 13 | Reload/restart persistence | Records remain available | PASS — stored in `storage/app/private/*.json` |

Notes:
- File locking uses `flock(LOCK_EX)` in `JsonStorageService::mutate()`.
- Availability is rechecked inside the lock before a booking is saved.
- CSRF tokens are present on all POST forms; admin routes use middleware.
