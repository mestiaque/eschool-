# School Management

Laravel package for running a school: academics, students, teachers, fees, exams,
library, transport, inventory, reports. It runs inside a host Laravel app with the
**metheme** theme, which provides login, users, roles, mail/SMS configuration and
the activity log.

## Install

```bash
composer require mestiaque/school-management
php artisan migrate
# Optional: ready-made roles (Office Admin, Accountant, Librarian, Exam Controller).
# Safe to re-run; it never changes a role you already configured.
php artisan db:seed --class="ME\SchoolManagement\Database\Seeders\SchoolRolesSeeder"
```

After login, users land on the school dashboard (override with `METHEME_HOME_ROUTE`).

## Things to know

- **Permissions**: every route checks a `module.action` permission from
  `src/Config/permissions.php` (see `src/Http/Middleware/SchoolPermission.php`).
  Grant them to roles on the theme's Roles page. Teachers linked to a login get
  their own "My …" pages.
- **Deleting**: students, teachers and staff go to *Settings → Recycle Bin* and can be
  restored. Classes, years, fee types, exams, etc. cannot be deleted while
  anything still uses them.
- **Notifications**: SMS / e-mail go through the theme's SMS and Mail configuration.
  Choose the automatic ones in *Settings → Notifications*.
- **Salaries** are posted to Expenses automatically (category in *Settings → General*),
  so reports count them once.
- **Language**: Bangla text lives in `src/resources/lang/bn/school-management.php`;
  use `sm_t('English text')` in views. Money and dates: `sm_money()`, `sm_date()`.

## Tests

Tests run inside the host app against a separate `school_test` database
(created once with `CREATE DATABASE school_test`; it is migrated and seeded with
demo data automatically).

```bash
../app/vendor/bin/phpunit          # PHPUnit feature tests
tests/browser/run.sh               # every page in headless Chrome + interaction checks
```

Set `SM_HOST_APP` if the host app is not at `../app`.
