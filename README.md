# SRSSMS

SMS platform for sending messages, managing contacts, wallets, and gateways — with separate administrator and user panels.

Built for Persian (Farsi) locales with Jalali dates, OTP login, prepaid wallets, and a token-based SMS API.

## Features

### Administrator panel
- **User management** — users, roles, permissions, impersonation, wallets, deposits, withdrawals
- **Finance** — currencies, wallets, transactions, deposits, withdrawals, payment gateways & settings
- **SMS** — providers, gateways, gateway–user assignment, messages, SMS settings
- **System** — general/site settings, system functions, backups
- **Support** — ticket inbox and replies

### User panel
- **SMS** — compose & send, message history, API tokens, docs & samples, request logs
- **Phonebook** — contacts, groups, tags, notes
- **Wallet** — balance, charge (online payment), transaction history
- **Support** — create and track tickets
- **Settings** — profile and password

### Platform
- Public welcome / landing page (configurable)
- Auth: login, register, OTP, forget password
- SMS send API (`/api/sms/send`) and provider webhooks
- Online payment callback for wallet deposits
- Spatie permissions, Excel import/export, log viewer, database backups

## Tech stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 |
| UI | Livewire 4 (SFC pages), Flux UI / Flux Pro, Tailwind CSS 4, Alpine.js |
| Auth & access | Spatie Laravel Permission, one-time passwords (OTP) |
| Dates | morilog/jalali, FluxUI Persian date picker |
| Validation | sadegh19b/laravel-persian-validation |
| Payments | shetabit/payment |
| SMS drivers | Log (dev), Sabanovin |
| Data | MySQL, Redis (cache + queue) |
| Other | Maatwebsite Excel, Spatie Settings / Backup / Tags, Vazirmatn font |

## Requirements

- PHP 8.3+ with extensions required by Laravel
- Composer 2
- Node.js 20+ (npm)
- MySQL 8+
- Redis (queue and cache)

## Installation

```bash
git clone <repository-url> srssms
cd srssms

composer install
cp .env.example .env
php artisan key:generate
```

Configure `.env` (at minimum):

```env
APP_NAME=SRSSMS
APP_URL=http://localhost:8000
APP_LOCALE=fa

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=srssms
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_HOST=127.0.0.1

SMS_DRIVER=log
```

Then:

```bash
php artisan migrate
npm install
npm run build
php artisan storage:link
```

Or use the Composer setup script (installs deps, `.env`, key, migrate, npm build):

```bash
composer setup
```

## Development

Run the app, queue worker, logs, and Vite together:

```bash
composer dev
```

Equivalent pieces separately:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

Tests:

```bash
composer test
# or
php artisan test
```

Code style:

```bash
vendor/bin/pint
```

## Panels & URLs

| Area | Path |
| --- | --- |
| Home | `/` |
| Login / Register / OTP | `/login`, `/register`, `/otp` |
| Admin dashboard | `/panels/administrator/dashboard` |
| User dashboard | `/panels/user/dashboard` |
| SMS API | `POST/GET /api/sms/send` |
| SMS webhook | `POST /api/sms/webhook/{provider}/{type}` |
| Payment | `/payment/pay/{deposit}`, `/payment/callback/{deposit}` |

Administrator routes are guarded by the `administrator` middleware and Spatie permissions (e.g. `sms-management.message.view`).

## Project structure

```
app/
  Enums/              Domain enums (SMS, finance, …)
  Http/Controllers/   Payments, SMS API & webhooks
  Jobs/               Queued SMS work
  Livewire/Forms/     Livewire form objects
  Models/             User, Finance, Sms, Phonebook, Support
  Services/           Auth, Permission, SMS drivers
  Settings/           Spatie settings (general, SMS, payment, …)
resources/views/
  pages/              Livewire SFC pages (`pages::…`)
    auth/
    panels/administrator/
    panels/user/
lang/
  fa/                 Persian translations (app, permissions, …)
  en/
routes/
  web.php             Panels & payment
  auth.php            Auth routes
  api.php             SMS API & webhooks
```

Conventions used in this project:

- Livewire **single-file components** under `resources/views/pages` (`pages::` namespace)
- Create/edit via **Flux flyout modals** (`position="right"`)
- Tables with search/filters; toast feedback after actions
- Permissions defined and labeled in `lang/fa/permissions.php` and `lang/en/permissions.php`
- User-facing strings in `lang/fa/app.php` (and related lang files)

## SMS API (overview)

Authenticated clients send SMS via API tokens managed in the user panel (`/panels/user/sms/tokens`), with docs and samples at:

- `/panels/user/sms/tokens/doc`
- `/panels/user/sms/tokens/sample`

Webhook endpoint for delivery/status callbacks:

```text
POST /api/sms/webhook/{provider}/{type}
```

Set `SMS_DRIVER=log` locally; configure real providers/gateways in the administrator SMS management UI.

## Configuration notes

- **Locale**: set `APP_LOCALE=fa` for Persian UI; Jalali dates are used throughout.
- **Queue**: SMS and related jobs expect Redis — run a worker (`composer dev` or `queue:listen`).
- **Payments**: configure gateways under administrator finance payment settings.
- **Site content**: general, welcome, contact, and social settings live under system management.

## License

MIT
