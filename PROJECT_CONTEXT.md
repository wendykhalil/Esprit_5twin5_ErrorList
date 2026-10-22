# SolarShare — project context

This document describes the **SolarShare** application in `Esprit_5twin5_ErrorList` as implemented in the repository. It is written so another coding assistant can continue work without rediscovering the architecture.

Statements below are taken from source, migrations, routes, configuration, and tests. Items that were not fully verified are marked **Unverified** or **Not inspected line by line**.

There is **no separate HTTP API**. The application is a Laravel web app: Blade pages, session authentication, CSRF, and redirects. Sending `Accept: application/json` makes Laravel FormRequest validation return HTTP 422 with `{ message, errors }` instead of a redirect. That behavior is covered by feature tests for login, registration, provider profiles, and service requests.

---

## Table of contents

1. [Project overview](#1-project-overview)
2. [Technologies and versions](#2-technologies-and-versions)
3. [Folder structure](#3-folder-structure)
4. [Architecture and request workflow](#4-architecture-and-request-workflow)
5. [Frontend structure and navigation](#5-frontend-structure-and-navigation)
6. [Backend modules and business rules](#6-backend-modules-and-business-rules)
7. [Routes](#7-routes)
8. [Database](#8-database)
9. [Authentication, roles, and authorization](#9-authentication-roles-and-authorization)
10. [Validation rules](#10-validation-rules)
11. [UI conventions](#11-ui-conventions)
12. [Environment and setup](#12-environment-and-setup)
13. [Commands](#13-commands)
14. [Tests](#14-tests)
15. [Known limitations and technical debt](#15-known-limitations-and-technical-debt)
16. [Conventions to preserve](#16-conventions-to-preserve)
17. [Dependencies and regression risks](#17-dependencies-and-regression-risks)
18. [Inspection coverage](#18-inspection-coverage)

---

## 1. Project overview

SolarShare is a university group project (Esprit, team 5twin5). It is a peer-to-peer platform for renewable-energy equipment in Tunisia: browse and rent equipment, pay (simulated), reserve, inspect handoff/return condition, open support tickets, and request technical interventions from approved providers.

The README (`README.md`) describes these modules:

- Authentication (Laravel Breeze)
- Support / réclamation
- Equipment
- Payments and transactions
- Reservations and inspections
- Technical services (providers and service requests)
- Admin back office

Authors listed in the README: Salma Briki, Ouaiss Aeiechi Chaouch, Marwen Jemai, Med Khalil Wendy, Med Amine Graja.

The product UI is **French**. Code, route names, and most status values are English, except reservation and inspection statuses, which are French identifiers (`en_attente`, `remise`, and so on).

---

## 2. Technologies and versions

Verified from `composer.json`, `composer.lock`, `package.json`, and `vite.config.js`.

| Piece | Constraint / locked version |
| --- | --- |
| PHP | `^8.2` in `composer.json`. The local machine used for recent work was PHP 8.5; that is an environment fact, not a project requirement. |
| Laravel | `^12.0`, locked at **v12.69.2** |
| Laravel Breeze | dev dependency, locked at **v2.4.2** (scaffolding already published into the app) |
| Laravel Tinker | `^2.10.1` |
| PHPUnit | `^11.5.3`, locked at **11.5.56** |
| Vite | `^6.0.11` |
| Tailwind CSS | `^4.3.3`, via `@tailwindcss/vite` |
| Alpine.js | `^3.4.2`, started in `resources/js/app.js` |
| Axios | `^1.7.4` (Breeze bootstrap; not a public API client in this app) |

Other Composer packages present as Laravel defaults: Faker, Pint, Sail, Pail, Collision, Mockery.

Not present: a `routes/api.php` file, Sanctum/Passport usage in application code, a SPA framework, or a payment gateway SDK.

Templating is **Blade**. Persistence is **Eloquent**. The default database driver in `config/database.php` is **SQLite** when `DB_CONNECTION` is unset. `.env.example` instead documents **MySQL** database `solarshare`. The README says to use SQLite locally. Both are supported by config; which one a given machine uses depends on `.env` and was not copied into this document.

---

## 3. Folder structure

```text
Esprit_5twin5_ErrorList/
├── app/
│   ├── Data/                  # AdminMockData (fake rentals), EquipmentData
│   ├── Enums/UserRole.php     # client, provider, admin
│   ├── Http/
│   │   ├── Controllers/       # Web controllers, Auth/, Admin/, Frontend/PaymentController
│   │   ├── Middleware/        # admin, client, provider aliases
│   │   └── Requests/          # Form requests, Auth/, Admin/, Concerns/
│   ├── Models/
│   ├── Policies/SupportTicketPolicy.php
│   ├── Providers/AppServiceProvider.php   # empty boot/register
│   ├── Rules/                 # HourlyRate, TunisianPhoneNumber, NoUnsafeCharacters, PasswordWithinHashLimit
│   ├── Services/UserRoleService.php
│   └── View/Components/       # AppLayout, GuestLayout, AuthLayout
├── bootstrap/app.php          # routing, middleware aliases, health /up
├── config/                    # Laravel config; database default sqlite
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/                    # index.php, images/solarshare-logo.png, Vite build output
├── resources/
│   ├── css/app.css            # Tailwind v4 + global input focus styles
│   ├── js/app.js              # Alpine start
│   └── views/                 # Blade: frontend, backend, auth, layouts, components
├── routes/web.php             # all public and authenticated pages
├── routes/admin.php           # /admin, required from web.php
├── routes/auth.php            # Breeze auth
├── routes/console.php
└── tests/Feature and tests/Unit
```

Important files:

- `bootstrap/app.php` registers middleware aliases `admin`, `client`, `provider` and the health route `/up`. Web routes come only from `routes/web.php`.
- `routes/web.php` is the map of the product. `routes/admin.php` is included at the bottom of it, before `routes/auth.php`.
- `app/Services/UserRoleService.php` is the only application service. It syncs `users.role` when a provider profile is approved, rejected, or deleted. It never changes an admin.
- `app/Http/Controllers/CategoryController.php` exists and is **not referenced by any route**.

---

## 4. Architecture and request workflow

```text
Browser
  → public/index.php
  → Laravel HTTP kernel (web middleware: session, CSRF, TrimStrings, ConvertEmptyStringsToNull)
  → route in routes/web.php, routes/admin.php, or routes/auth.php
  → optional middleware: auth, verified, admin, client, provider
  → FormRequest (authorize, prepareForValidation, rules) when the controller type-hints one
  → Controller
  → Eloquent models
  → Blade view or redirect with session flash (success / error / info / status)
```

There is no queue worker requirement for the features inspected. `composer.json` script `dev` starts `queue:listen`, but the inspected features do not dispatch jobs.

Mail is the Laravel default (used by Breeze password reset and email verification). Tests force `MAIL_MAILER=array`.

Files:

- Equipment images and profile photos use the `public` disk (`storage/app/public`), exposed as `/storage/...` after `php artisan storage:link`.
- Support ticket images are stored with `->store('support-tickets', 'public')`. The URL is `asset('storage/'.$path)` from `SupportTicket::attachmentUrl()`.

Responses:

- Normal browser posts: **302** back to the form with validation errors in the session, or **302** to the next page with a flash message.
- JSON clients (`postJson` / `Accept: application/json`): **422** `{ message, errors }` when validation fails.
- Authorization failures: **403** (`abort`) or a redirect with an error flash, depending on the controller. Do not unify these without checking each action; the UX is mixed on purpose in the technical-services module.

---

## 5. Frontend structure and navigation

### Layouts

| Layout | Used for |
| --- | --- |
| `resources/views/layouts/frontend.blade.php` | Public site. French `lang`, Fraunces font, navbar + footer, Vite assets. |
| `resources/views/layouts/auth.blade.php` via `<x-auth-layout>` | Login and registration only. |
| `resources/views/layouts/guest.blade.php` | Remaining Breeze screens: forgot password, reset, verify email, confirm password. These were not redesigned with the SolarShare auth layout. |
| `resources/views/layouts/app.blade.php` | Breeze profile and `/dashboard`. |
| `resources/views/layouts/backend.blade.php` | Admin back office. |

`resources/views/frontend/auth/login.blade.php` and `register.blade.php` are **unused**. No PHP `view('frontend.auth...')` call exists. Live auth pages are `resources/views/auth/login.blade.php` and `register.blade.php`.

### Public navigation

`resources/views/components/frontend/navbar.blade.php`:

- Accueil → `home`
- Équipements → `equipments.index`
- Services → `service-providers.index`
- Comment ça marche → `how-it-works`
- À propos → `about`

Guest actions: Connexion, Inscription. The navbar logo is an inline SVG plus the wordmark “SolarShare”, not the PNG file.

Authenticated menu (desktop and mobile) includes, when the role matches:

- Profile (`profile.edit`)
- Mes demandes (`service-requests.index`) for clients
- Demandes reçues (`provider.service-requests.index`) for providers
- Administration (`admin.dashboard`) for admins
- Réservations, Inspections, Paiements, Factures, Support
- Logout (`POST logout`)

The navbar hides the desktop cluster below `md` and shows a mobile menu. The nav is `sticky` with `z-index: 1000`.

### Page groups

**Public**

- `frontend/home.blade.php` — categories from the database
- `frontend/equipments/index.blade.php`, `show.blade.php`
- `frontend/service-providers/index.blade.php`, `show.blade.php` — approved providers only
- `frontend/about.blade.php`, `how-it-works.blade.php`, `contact.blade.php` — static

**Authenticated customer**

- `frontend/equipments/edit.blade.php` — owner edit; creation is admin-only
- `reservations/*` — real reservation CRUD (not under `frontend/`)
- `inspections/*` — real inspection CRUD
- `frontend/rentals/index.blade.php` — route `rentals.index` (`/mes-reservations`). This is a **separate** page from `/reservations`.
- `frontend/payments/*` — create, show, history, invoices, invoice
- `frontend/tickets/*` — support
- `frontend/service-providers/create.blade.php`, `edit.blade.php`
- `frontend/service-requests/*`
- `frontend/provider-requests/*` — provider inbox
- `profile/edit.blade.php`

**Admin** (`resources/views/backend/`)

Dashboard, equipments, users, rentals (mock), reservations, inspections, payments, refund, transactions, tickets, service providers, settings.

### Design split

The UI was built by several people and is not one design system:

- Public marketing and navbar: Tailwind **green** (`green-600`, `green-800`), font **Fraunces** for the wordmark.
- Login and register: dark green `#0C6030`, bright green `#189C48`, logo `public/images/solarshare-logo.png`, split layout.
- Back office and several service screens: **amber-500** buttons, slate borders, font **Outfit** set inline on many headings.
- Global form focus in `resources/css/app.css` forces **blue** rings with `!important` inside `@layer base`. Auth pages and the intervention form override that with page-level CSS. New forms that need a non-blue focus must override the same way, or the global rule wins.

Responsive behavior that is implemented: navbar switches at the `md` breakpoint; many forms use `sm:flex-row` for action rows. There is no separate mobile design system.

---

## 6. Backend modules and business rules

### 6.1 Authentication

- Registration (`RegisteredUserController@store`) creates a user with `role = client` only. It fires the `Registered` event and **does not log the user in**. Redirects to login with: `Compte créé avec succès. Vous pouvez maintenant vous connecter.`
- Login (`AuthenticatedSessionController@store`) regenerates the session, then redirects to `intended` or:
  - admin → `admin.dashboard`
  - provider → `provider.service-requests.index`
  - everyone else → `home`
- Logout redirects to `home`.
- `User` does **not** implement `MustVerifyEmail` (the import is commented out). The `/dashboard` route still uses the `verified` middleware. Email verification routes exist.
- Failed login message is generic: `Ces identifiants ne correspondent pas.` The same text is used for an unknown email and a wrong password.
- Login is rate-limited (Breeze `RateLimiter`, 5 attempts). The message starts with `Trop de tentatives.`

### 6.2 Equipment

- Table name is `equipment` (singular), model `Equipment`.
- Public list `EquipmentController@index` filters by `q`, `location`, `category`, `maxPrice`, `available`, and sort `price_asc` / `price_desc`. It does **not** filter `status = active`. Inactive rows can appear in the public catalog.
- Show is public. Edit, update, and delete require `auth` and abort 403 unless `equipment.user_id` is the current user.
- **Creation is admin-only**: `GET/POST /admin/equipements`. The comment in `routes/web.php` says frontend creation was removed. Admin create is a 4-step wizard (`validateWizardStep`) then `store`.
- Images: nullable, jpg/jpeg/png/webp, max 2048 KB.

### 6.3 Categories

`categories` rows are seeded and used as a foreign key and as filters. `CategoryController` is unused. There is no public category CRUD route.

### 6.4 Reservations

`ReservationController` (user) and `Admin\ReservationController` (admin) both persist the `reservations` table.

User rules:

- `user_id` is set from `auth()->id()`, not from the form.
- New status is always `en_attente`.
- Price is `max(days, 1) * equipment.price_per_day` (`calculerPrix`). The method also looks at non-existent attributes `prix_jour` and `price` as fallbacks; the column that exists is `price_per_day`.
- Overlapping reservations on the same equipment are rejected unless the other row is `annulee` or `terminee`.
- Show, edit, update, and delete abort 403 if the reservation is not owned by the current user.
- The user form lists `Equipment::all()`, not only the user's equipment and not only available items.

Statuses: `en_attente`, `confirmee`, `en_cours`, `terminee`, `litige`, `annulee`.

`/mes-reservations` (`rentals.index`) renders `frontend.rentals.index` and is not the Eloquent reservation list. The real list is `/reservations`.

### 6.5 Inspections

One inspection per reservation and type. Types: `remise`, `retour`. Unique index `(reservation_id, type)`.

Condition (`etat`): `neuf`, `bon`, `use`, `endommage`. Battery level 0–100, optional. Observations max 1000.

The user FormRequest limits `reservation_id` to reservations owned by the current user. Admin uses `InspectionAdminRequest` and can manage any inspection.

### 6.6 Payments and transactions

This is a **simulation**. No card is sent to a processor. Card fields are validated and then discarded; they are not stored.

`Frontend\PaymentController`:

- `create` requires query `amount` numeric and `> 0`, otherwise redirects to the equipment catalog.
- `store` accepts `method` `card`, `cash`, or `bank_transfer`.
- Card and cash become payment `paid` and transaction `completed`.
- Bank transfer stays `pending` / `pending`.
- A transaction reference is `TXN-` + random + payment id.
- Show and invoice abort 403 if `payment.user_id` is not the current user.

`payments` has **no** `reservation_id` or `equipment_id`. A payment is not linked to a reservation in the schema. `equipment_id` may be passed as a query string into the payment form; it is not a validated foreign key in `store`.

Admin payment CRUD can mark refunds. Refund creates a reversing transaction when the payment is `paid`. Exact refund arithmetic was not re-read line by line beyond the status guards in `Admin\PaymentController`.

Admin transaction CRUD is a separate resource on the `transactions` table.

### 6.7 Support tickets

Client (`SupportTicketController`), prefix `/support`:

- Create subject 5–150, message 20–5000, priority `low|normal|high` (default `normal`), optional image.
- Reference is rewritten after insert to `TCK-` + zero-padded id.
- Show uses `SupportTicketPolicy::view` (owner or admin).
- Replies are blocked when status is `closed` (`acceptsReplies()`).
- Clients only see replies where `is_internal` is false (`publicReplies`).

Admin:

- List, show, reply, patch status.
- Internal notes exist (`ticket_replies.is_internal`).
- `resolved` and `closed` set `closed_at` if it was null; other statuses clear it (`syncClosedTimestampForStatus`).

Policy is auto-discovered by Laravel's naming convention (`SupportTicket` → `SupportTicketPolicy`). `AppServiceProvider` does not register it manually.

### 6.8 Service providers

Public index and show only include `status = approved`. A non-approved profile returns **404** on show, not 403.

Create/store is behind middleware `client` and `StoreServiceProviderRequest`:

- Only a client may submit. Admins and providers get 403 from middleware before the controller. The controller also redirects an admin who somehow reaches `create`.
- One profile per user (`service_providers.user_id` unique). A second create redirects to edit.
- Stored status is always `pending`. `user_id` comes from the session.
- The profile is not public until an admin approves it.

Admin approve sets `status = approved` and `UserRoleService::syncAfterProviderApproval` sets the user role to `provider` unless the user is admin.

Admin reject sets `status = rejected` and sets the role back to `client` (admins unchanged).

Owner update changes specialty, description, experience, phone, location, rate, and availability. It does **not** change `status`. Protected body fields `status`, `user_id`, `id`, and `role` are rejected by the FormRequest.

Delete is owner-only and calls `syncAfterProviderProfileDeleted` (role back to client).

### 6.9 Service requests

Customer (`auth`, not restricted to `client`):

- Target provider must be `approved` and `availability = true`, otherwise redirect to the provider page with an error.
- A user cannot request their own provider profile.
- Fields: title, date, address, optional equipment, description.
- `user_id` is the authenticated user. `service_provider_id` is the route model. `status` is always `pending`. `estimated_price` is always null.
- Equipment, when present, must belong to the authenticated user.
- The customer may delete (cancel) only while `pending`.
- Show aborts 403 if the request is not theirs.

Provider (`middleware provider`, which also requires an approved profile):

- `pending` → accept (requires `estimated_price`) → `accepted`
- `pending` → reject → `rejected`
- `accepted` → start → `in_progress`
- `in_progress` → complete → `completed`

Accept price validation is inline in the controller: `required|numeric|min:0|max:999999.99`. It is not a FormRequest.

---

## 7. Routes

Named routes below match `routes/web.php`, `routes/admin.php`, and `routes/auth.php`. Middleware is noted when it is not public.

### Public and authenticated site

| Method | Path | Name | Middleware |
| --- | --- | --- | --- |
| GET | `/` | `home` | |
| GET | `/up` | health | framework |
| GET | `/equipements` | `equipments.index` | |
| GET | `/equipements/{equipment}` | `equipments.show` | numeric id |
| GET | `/equipements/{equipment}/edit` | `equipments.edit` | auth |
| PUT | `/equipements/{equipment}` | `equipments.update` | auth |
| DELETE | `/equipements/{equipment}` | `equipments.destroy` | auth |
| GET | `/a-propos` | `about` | |
| GET | `/comment-ca-marche` | `how-it-works` | |
| GET | `/contact` | `contact` | |
| GET | `/mes-reservations` | `rentals.index` | auth |
| GET | `/reservations` | `reservations.index` | auth |
| resource | `/reservations` except index | `reservations.*` | auth |
| resource | `/inspections` | `inspections.*` | auth |
| GET | `/paiement/creer` | `payments.create` | auth |
| POST | `/paiement` | `payments.store` | auth |
| GET | `/paiement/{payment}` | `payments.show` | auth |
| GET | `/mes-paiements` | `payments.history` | auth |
| GET | `/mes-factures` | `payments.invoices` | auth |
| GET | `/mes-paiements/{payment}/facture` | `payments.invoice` | auth |
| GET | `/dashboard` | `dashboard` | auth, verified |
| GET/POST | `/support`, `/support/create`, `/support/{supportTicket}` | `support.tickets.*` | auth |
| POST | `/support/{supportTicket}/replies` | `support.tickets.replies.store` | auth |
| GET/PATCH/DELETE | `/profile` | `profile.edit`, `profile.update`, `profile.destroy` | auth |
| DELETE | `/profile/photo` | `profile.photo.delete` | auth |
| GET | `/profil` | `profile` | auth, same action as edit |
| GET | `/services` | `service-providers.index` | |
| GET | `/services/create` | `service-providers.create` | auth, client |
| POST | `/services` | `service-providers.store` | auth, client |
| GET/PUT/DELETE | `/services/{serviceProvider}` edit/update/destroy | `service-providers.edit/update/destroy` | auth |
| GET | `/services/{serviceProvider}` | `service-providers.show` | public, after the more specific routes |
| GET/POST | `/services/{serviceProvider}/request` | `service-requests.create/store` | auth |
| GET | `/my-service-requests` | `service-requests.index` | auth |
| GET/DELETE | `/my-service-requests/{serviceRequest}` | `service-requests.show/destroy` | auth |
| GET | `/provider/service-requests` | `provider.service-requests.index` | auth, provider |
| GET | `/provider/service-requests/{serviceRequest}` | `provider.service-requests.show` | auth, provider |
| PATCH | `.../accept`, `/reject`, `/start`, `/complete` | `provider.service-requests.*` | auth, provider |

Route order matters: `/services/create` is registered before `/services/{serviceProvider}`. Equipment `{equipment}` routes use `whereNumber`.

### Admin (`auth` + `admin`, prefix `/admin`, names `admin.*`)

| Area | Paths |
| --- | --- |
| Dashboard | `GET /admin` `admin.dashboard`, `GET /admin/dashboard/stats` `admin.dashboard.stats` |
| Equipment | `/admin/equipements` index, create, `POST .../create/validate-step`, store, edit, update, destroy |
| Users | `/admin/utilisateurs` index, show, edit, update, destroy |
| Rentals | `GET /admin/locations` `admin.rentals` — mock data |
| Reservations | full CRUD `/admin/reservations` |
| Inspections | `Route::resource('inspections')` → `/admin/inspections` |
| Settings | `GET /admin/parametres` `admin.settings` — view only |
| Payments | resource `/admin/payments` plus `GET/POST /admin/payments/{payment}/refund` |
| Transactions | resource `/admin/transactions` |
| Tickets | index, show, reply, patch |
| Providers | index, show, `PATCH .../approve`, `PATCH .../reject` |

### Auth (Breeze)

Guest: `register`, `login`, `password.request`, `password.email`, `password.reset`, `password.store`.

Auth: `verification.notice`, `verification.verify` (signed, throttle 6/min), `verification.send`, `password.confirm`, `password.update`, `logout`.

---

## 8. Database

### 8.1 Engine

- Config default: SQLite file `database/database.sqlite` when `DB_DATABASE` is empty (`config/database.php`).
- `.env.example`: MySQL, database name `solarshare`, user `root`, empty password.
- PHPUnit: SQLite `:memory:` (`phpunit.xml`).
- Foreign keys are enabled for SQLite via `DB_FOREIGN_KEYS` default true.

Do not assume MySQL-only SQL. The role migration uses `->change()` and a data update from `owner` to `client`.

### 8.2 Tables

**users**

- `id`, `name`, `email` unique, `email_verified_at` nullable, `password`, `remember_token`, timestamps
- Profile: `phone`, `address`, `city`, `country`, `bio`, `profile_photo` nullable
- `role` string(20) default `client`. Historically an enum `client|owner|admin`. Migration `2026_10_08_120000_update_users_role_for_providers` rewrites `owner` → `client`, changes the column to string, then sets `provider` for users who already have an approved provider profile (admins excluded).

**password_reset_tokens**, **sessions**, **cache**, **cache_locks**, **jobs**, **job_batches**, **failed_jobs** — Laravel defaults.

**categories** — `name` unique, `description` nullable.

**equipment**

- `user_id` → users, cascade delete
- `category_id` → categories, cascade delete
- `name`, `description`, `brand` nullable
- `power` decimal(10,2) nullable, `capacity` decimal(10,2) nullable
- `condition` enum `excellent|good|used`
- `price_per_day` decimal(10,2)
- `location`, `availability` boolean default true
- `image` nullable
- `status` enum `active|inactive` default `active`

**reservations**

- `user_id` cascade, `equipment_id` cascade (constrained to the equipment table name)
- `date_debut`, `date_fin` dates
- `statut` enum default `en_attente`: `en_attente|confirmee|en_cours|terminee|litige|annulee`
- `prix_total` decimal(8,2) default 0

**inspections**

- `reservation_id` cascade
- `type` enum `remise|retour`
- `etat` enum `neuf|bon|use|endommage`
- `niveau_batterie` unsigned tinyint nullable
- `observations` nullable text
- `date_inspection` datetime
- unique (`reservation_id`, `type`)

**payments**

- `user_id` nullable, cascade (added later)
- `amount` decimal(10,2)
- `method` string default `card`
- `status` enum `pending|paid|failed|refunded` default `pending`
- `payment_date` nullable, `description` nullable

**transactions**

- `payment_id` cascade
- `reference` unique
- `type` string
- `amount` decimal(10,2)
- `status` enum `pending|completed|failed|refunded` default `pending`
- `transaction_date` nullable
- `description` nullable text

**support_tickets**

- `user_id` cascade
- `reference` unique
- `subject` string(150), `message` text, `attachment` string nullable
- `priority` enum `low|normal|high` default `normal`
- `status` enum `open|in_progress|resolved|closed` default `open`
- `closed_at` nullable
- Extra indexes from `2026_10_05_170001_add_indexes_to_support_tables`

**ticket_replies**

- `support_ticket_id` cascade, `user_id` cascade
- `body` text, `is_internal` boolean default false

**service_providers**

- `user_id` unique, cascade
- `specialty` string, `description` text, `experience_years` integer
- `phone` nullable, `location` string
- `hourly_rate` decimal(8,2)
- `availability` boolean default true
- `status` string default `pending` (not an enum). Application values used in code: `pending`, `approved`, `rejected`.

**service_requests**

- `user_id` cascade
- `service_provider_id` restrict on delete
- `equipment_id` nullable, null on delete
- `title` string, `description` text, `requested_date` datetime, `address` string
- `status` string default `pending`. Application values: `pending`, `accepted`, `rejected`, `in_progress`, `completed`.
- `estimated_price` decimal(8,2) nullable

### 8.3 Relationships

```text
User 1—1 ServiceProvider
User 1—* Equipment, Payment, SupportTicket, ServiceRequest
Category 1—* Equipment
Equipment 1—* Reservation, ServiceRequest
Reservation 1—* Inspection
Payment 1—* Transaction
SupportTicket 1—* TicketReply
ServiceProvider 1—* ServiceRequest
ServiceRequest *—1 Equipment (optional)
```

`User::$fillable` includes `role`. Registration does not mass-assign the request's role; it sets `UserRole::Client` explicitly. Future `User::create($request->all())` would be unsafe.

Password cast is `'hashed'`. Registration also calls `Hash::make`. Laravel's hashed cast does not hash an already-hashed value again. Keep that behavior; do not remove only one of the two without checking tests.

### 8.4 Seeders

`DatabaseSeeder` creates `demo@solarshare.tn` only when the users table is empty, then calls:

`AdminUserSeeder`, `CategorySeeder`, `EquipmentSeeder`, `ReservationSeeder`, `InspectionSeeder`, `SupportTicketSeeder`, payment/transaction factories (10 payments, 1–3 transactions each), `ServiceProviderSeeder`, `ServiceRequestSeeder`.

`AdminUserSeeder` upserts an admin user. The email and password are PHP constants on that class (`ADMIN_EMAIL`, `ADMIN_PASSWORD`). **Do not copy the password into docs, tickets, or new seeders.** The email constant in source is `admin@solarshare.tn`. Re-run with `php artisan db:seed --class=AdminUserSeeder`.

---

## 9. Authentication, roles, and authorization

Enum `App\Enums\UserRole`: `client`, `provider`, `admin`. Labels: Client, Prestataire, Administrateur.

Helpers on `User`: `isAdmin()`, `isClient()`, `isProvider()`, `roleLabel()`.

| Middleware | Rule |
| --- | --- |
| `auth` | Laravel session guard `web` |
| `admin` | `isAdmin()`, else 403 `Accès réservé aux administrateurs.` |
| `client` | `isClient()`, else 403 `Cette action est réservée aux comptes client.` |
| `provider` | `isProvider()` and an approved `serviceProvider` profile, else 403 |

Role changes:

- Register → client.
- Admin approves provider profile → provider (unless the user is already admin).
- Admin rejects profile, or owner deletes profile → client (admins unchanged).
- Admin user edit (`UpdateAdminUserRequest`) can set any `UserRole` value, but `provider` is rejected unless that user already has an **approved** provider profile.

There is no permission table and no policy besides `SupportTicketPolicy`. Other ownership checks are `abort(403)` or `abort_unless` inside controllers.

`EnsureUserIsClient` runs before the provider-create controller. An admin never reaches the controller's "admins should use the back office" redirect on `POST /services`; they receive 403.

---

## 10. Validation rules

Patterns used by the newer forms (login, register, provider profile, service request):

- `novalidate` on the Blade form. HTML `required`, `min`, `max`, and `type="email"` / `type="number"` are not the enforcement mechanism.
- `prepareForValidation` trims strings. Passwords are not trimmed.
- Emails are trimmed and lowercased before `email:rfc` and `unique`.
- `bail` so the first failure message is the one shown.
- French `messages()`.
- Unexpected keys are rejected. `role` on auth forms has its own message in `RejectsUnexpectedFields`. Provider and service-request forms reject `status`, ids, and price fields with `Ce champ ne peut pas être modifié depuis ce formulaire.`
- Do not disable the submit button on the submit event. Chrome cancels the POST. The existing pattern is an Alpine `submitting` flag that `preventDefault`s only a second submit.

### Login — `LoginRequest`

- `email`: required string, `email:rfc`, max 255
- `password`: required string, `PasswordWithinHashLimit` (72 bytes, `strlen`, no minimum)
- Allowed keys: `email`, `password`, `remember`, `_token`
- `remember` must be scalar if present

### Register — `RegisterRequest`

- `name`: required string, max 255, trimmed
- `email`: same as login, plus unique users
- `password`: `Password::defaults()` (minimum 8; no custom rules in `AppServiceProvider`) and max 72 bytes
- `password_confirmation`: required, `same:password`
- Allowed keys: `name`, `email`, `password`, `password_confirmation`, `_token`
- A submitted `role` is rejected

### Provider profile — `StoreServiceProviderRequest`

| Field | Rule |
| --- | --- |
| specialty | required string, 2–255, no control characters |
| location | required string, 2–255, no control characters |
| experience_years | whole number 0–60, 0 allowed |
| hourly_rate | `HourlyRate`: positive, at most 2 decimals, max `999999.99`, normalized to a 2-decimal string. Comma is accepted as decimal separator when there is no dot. |
| phone | optional. Tunisian: 8 national digits starting with 2, 3, 4, 5, 7, or 9. Prefixes `+216`, `00216`, or `216` when length is 11. Stored as `+216` plus 8 digits. Empty becomes null. |
| description | required, 30–2000, newlines allowed, other control characters rejected |
| availability | boolean. The checkbox must submit `1`. Laravel's `boolean` rule rejects the default checkbox value `on`. |

`authorize`: POST requires `isClient()`. Update requires the route profile to belong to the user (403 before validation for an intruder).

### Service request — `StoreServiceRequest`

| Field | Rule |
| --- | --- |
| title | required string, 3–255, no control characters |
| requested_date | required `Y-m-d`, `after_or_equal:today` |
| address | required string, 5–255 |
| equipment_id | optional integer that exists on `equipment` for the current user. Empty string becomes null. |
| description | required, 30–2000, newlines allowed |

Protected and rejected if posted: `status`, `estimated_price`, `user_id`, `service_provider_id`.

Availability and self-request checks run in the controller **after** validation. Invalid data on an unavailable provider returns validation errors. Valid data on an unavailable or self-owned provider redirects with the French flash error and saves nothing.

### Equipment — `StoreEquipmentRequest` (admin)

`category_id` exists, `name` 3–255, `description` 10–1000, `brand` nullable max 255, `power` and `capacity` nullable numeric 0–999999, `condition` in `excellent|good|used`, `price_per_day` required numeric 0–99999, `location` required max 255, `status` `active|inactive`, `availability` nullable boolean, `image` nullable image jpg/jpeg/png/webp max 2048 KB.

Wizard steps: (1) name, category, description, brand, power, capacity, condition; (2) location; (3) image; (4) price, status, availability.

`UpdateEquipmentRequest` is the owner update path. It was not diffed field-by-field against the store rules in this pass.

### Reservation — `ReservationRequest`

`equipment_id` required exists, `date_debut` required date `after_or_equal:today`, `date_fin` required date `after:date_debut`, plus the overlap rule in section 6.4.

### Inspection — `InspectionRequest`

`reservation_id` must exist and belong to the current user. `type` in `Inspection::TYPES` and unique per reservation. `etat` in `Inspection::ETATS`. `niveau_batterie` nullable integer 0–100. `observations` nullable max 1000. `date_inspection` required date.

### Support — `StoreSupportTicketRequest`

`subject` 5–150, `message` 20–5000, `priority` nullable in `low|normal|high`, `attachment` nullable image jpg/jpeg/png/webp max 2048 KB.

### Admin user — `UpdateAdminUserRequest`

`name` 2–255, `email` unique ignoring the user, `role` in `UserRole::values()`, plus the approved-profile check for `provider`.

### Payment store (inline, not a FormRequest)

`amount` numeric `gt:0`, `method` in `card|cash|bank_transfer`, `description` nullable max 500. Card method requires holder max 100, 16 digits, expiry `MM/YY`, CVV 3 digits.

### Provider accept (inline)

`estimated_price` required numeric min 0 max 999999.99.

---

## 11. UI conventions

- User-facing copy is French, including validation messages.
- Field errors on the redesigned forms use the prefix **Erreur.** plus `aria-invalid`, so the error is not color alone.
- Optional fields are labeled `(facultatif)`.
- Success and error flashes use green and red alert boxes. Check the target Blade file; not every page renders `session('error')`.
- Preserve `old()` values after a 302 validation failure.
- Do not invent a logo. The official file is `public/images/solarshare-logo.png`. The public navbar still uses the inline SVG.
- Tailwind v4 scans Blade at build time. A new utility class is invisible until `npm run build` (or `npm run dev`). Arbitrary classes such as `lg:grid-cols-[...]` have been omitted from the build before; prefer standard utilities.
- Auth pages: `#0C6030` buttons, `#189C48` focus.
- Back office primary actions: `bg-amber-500`.
- Public navbar: `bg-green-600`.

---

## 12. Environment and setup

`.env.example` only documents:

- `APP_KEY` empty
- `APP_URL=http://127.0.0.1:8000`
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=solarshare`
- `DB_USERNAME=root`
- `DB_PASSWORD` empty

Laravel still expects the usual variables (`APP_NAME`, `APP_ENV`, `APP_DEBUG`, mail, session, and so on) because the framework config files call `env()`. A machine that only copies `.env.example` may miss those until `php artisan key:generate` and a full `.env` exist. **This document does not read or repeat `.env`.**

Filesystem: `FILESYSTEM_DISK` defaults to `local` (`storage/app/private`). Public uploads use the `public` disk explicitly in code. Create the link with `php artisan storage:link` (`public/storage` → `storage/app/public`).

SQLite local setup from the README: set `DB_CONNECTION=sqlite`, create `database/database.sqlite`, migrate, seed. PHP must have `pdo_sqlite` enabled or migrate fails with "could not find driver".

---

## 13. Commands

```bash
composer install
cp .env.example .env
php artisan key:generate
# SQLite, as the README requires:
# DB_CONNECTION=sqlite and an empty database/database.sqlite file
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan serve
```

Development (serves the app, queue listener, Pail, and Vite together):

```bash
composer run dev
```

Tests:

```bash
php artisan test
php artisan test --filter=ServiceRequestValidationTest
```

Frontend production assets:

```bash
npm run build
```

`public/build/manifest.json` must exist. Without it, Blade `@vite` throws `ViteManifestNotFoundException`.

Admin seeder alone:

```bash
php artisan db:seed --class=AdminUserSeeder
```

---

## 14. Tests

PHPUnit feature tests use `RefreshDatabase`, SQLite memory, and `withoutVite()` where views are rendered. They were **not re-executed** while writing this document. Method names below are from the test files.

| File | What it covers |
| --- | --- |
| `tests/Feature/Auth/AuthenticationTest.php` | Login screen, success, bad password, logout |
| `tests/Feature/Auth/RegistrationTest.php` | Register screen and successful registration |
| `tests/Feature/Auth/AuthValidationTest.php` | Login/register server rules, generic credential error, rate limit, no role escalation, 72-byte password |
| `tests/Feature/Auth/PasswordResetTest.php` | Breeze reset flow |
| `tests/Feature/Auth/PasswordUpdateTest.php` | Profile password update |
| `tests/Feature/Auth/PasswordConfirmationTest.php` | Confirm password |
| `tests/Feature/Auth/EmailVerificationTest.php` | Verification screen and hash |
| `tests/Feature/ProfileTest.php` | Breeze profile |
| `tests/Feature/ServiceProviderValidationTest.php` | Guest, non-client, whitespace, types, phone, rate, pending until admin approval, owner cannot set status |
| `tests/Feature/ServiceRequestValidationTest.php` | Guest, missing fields, date, foreign equipment, protected fields, self-request, unavailable provider, pending create |
| `tests/Feature/UserRoleSystemTest.php` | Client vs provider access, approval assigns provider role |
| `tests/Feature/SupportTicketTest.php` | Create, attachment stored on fake disk, privacy, replies, closed tickets, internal notes, admin status |
| `tests/Feature/AdminEquipmentStoreTest.php` | Admin equipment server validation |
| `tests/Feature/AdminEquipmentWizardStepTest.php` | Wizard step 1 |
| `tests/Feature/AdminUserManagementTest.php` | Provider role only with an approved profile |
| `tests/Feature/AdminUsersIndexTest.php` | User list reads the database |
| `tests/Feature/ExampleTest.php` | Home returns success |
| `tests/Unit/ExampleTest.php` | Placeholder |

Not covered by tests found in the tree: payment simulation, reservation overlap, inspection uniqueness, admin refunds, and the public equipment catalog filters.

---

## 15. Known limitations and technical debt

Verified in code unless marked otherwise.

1. **Two reservation UIs.** `/reservations` is the Eloquent CRUD. `/mes-reservations` is `frontend.rentals.index`. Admin `/admin/locations` uses `AdminMockData::getRentals()` and does not read `reservations`.
2. **Settings** (`/admin/parametres`) return a view and persist nothing.
3. **Payments are not tied to reservations or equipment** in the database. Card data is validated and not stored. Success copy says "Simulation de paiement".
4. **Public equipment list does not hide `inactive` rows.**
5. **`CategoryController` has no route.**
6. **`resources/views/frontend/auth/*` are unused.** Do not edit them expecting the live login page to change.
7. **Guest auth screens** (forgot, reset, verify, confirm) still use the Breeze guest layout, not `AuthLayout`.
8. **User role history.** The first role migration allowed `owner`. The later migration rewrites it. A status badge in `resources/views/components/backend/status-badge.blade.php` still has a `Propriétaire` label. The live role is `provider` / Prestataire.
9. **`users.role` is fillable.** Only some write paths set it explicitly.
10. **Service-request accept** still uses inline `$request->validate`, unlike create.
11. **Provider phone and rate rules** are strict (Tunisian numbers, two decimals). Do not replace them with generic `numeric` or `string` rules.
12. **Ticket images** are public-disk URLs. `SupportTicketTest` fakes the disk and does not request the image URL. A 403 when opening an attachment under `php artisan serve` was reported during development and **was not fixed or re-verified** in this documentation pass. Likely dependency: `php artisan storage:link`, and the PHP built-in server's handling of symlinks. Unverified as a root cause.
13. **`.env.example` is MySQL; README and config default are SQLite.** Follow the README for a fresh local setup, then align `.env`.
14. **Global blue focus** in `app.css` overrides Tailwind focus classes unless the page adds a stronger override.
15. **No REST API, no policies** except support tickets. Copy the existing controller `abort(403)` style rather than introducing a new authorization layer for one action.
16. **Contact page** is static. It does not create a support ticket.
17. **Dashboard** (`/dashboard`) is the Breeze view and is not the post-login destination. Login goes to admin, provider requests, or home.

---

## 16. Conventions to preserve

- French validation messages and flash messages. Do not switch existing screens to English.
- Server-side validation is the source of truth. Keep `novalidate` on forms that were converted. Do not put the only checks back into HTML attributes.
- Never let a client set `role`, `status`, `user_id`, `service_provider_id`, or `estimated_price` on the provider or service-request forms. Identity comes from `auth()` and the route model.
- New provider profiles stay `pending` and stay off the public directory until admin approval. Approval is what assigns the `provider` role.
- Service requests are created as `pending` with a null price. The provider sets the price only when accepting.
- Customers cancel service requests only while `pending`.
- Registration does not auto-login and does not accept a role.
- Login errors stay generic. Do not reveal whether the email exists.
- Do not trim passwords. Do trim and lowercase emails before uniqueness checks.
- Password maximum is 72 **bytes** (`strlen`), not 72 characters. Login has no minimum, so existing short passwords still work. Registration minimum is 8.
- Checkbox `availability` posts `1`.
- Equipment table is `equipment`, not `equipments`.
- Reservation and inspection status strings are the French enums in the migrations. Do not rename them in only one layer.
- Support ticket references are generated in the model `booted()` hook. Do not require the client to send `reference`.
- Internal ticket replies must stay hidden from `publicReplies`.
- Prefer FormRequest classes and explicit `$request->validated()` fields over `$request->all()`.
- Blade plus Alpine is the frontend. Do not introduce a second JS framework for one form.
- After Blade class changes, run `npm run build` if production assets are what the browser loads.
- Do not commit `.env`, database files, or `storage/logs`.

---

## 17. Dependencies and regression risks

| Change | What else breaks |
| --- | --- |
| Provider approval / rejection | `users.role` via `UserRoleService`. Provider routes then allow or 403. |
| Deleting a provider profile | Role returns to client. `service_requests.service_provider_id` is `restrictOnDelete`, so a provider who still has requests cannot be deleted at the database level. |
| Deleting a user | Cascades equipment, payments, tickets, service provider, service requests. Reservations cascade from the user. Inspections cascade from reservations. |
| Deleting equipment | Reservations cascade. Service requests keep the row and null `equipment_id`. |
| Changing reservation status names | User forms, admin forms, overlap query (`annulee`, `terminee`), and seeders. |
| Filtering the public catalog to `active` only | Any test or seeder that expects inactive equipment to be listed. |
| Replacing payment simulation with a real gateway | Card fields are currently not stored; history and invoices assume local `payments` rows. |
| Auth layout or login redirect | `AuthenticationTest` expects the current post-login destination (`home` for a normal user). |
| Disabling the submit button while submitting | The browser drops the first POST. The Alpine guard exists to avoid that. |
| Tailwind rebuild | Auth grid, error colors, and amber/green buttons depend on classes scanned from Blade. |
| `storage:link` or attachment URL shape | Ticket images and profile/equipment images. |
| Mass-assigning `User` with request input | `role` is fillable. |

---

## 18. Inspection coverage

Inspected for this document:

- `composer.json`, `composer.lock` version lines for Laravel, Breeze, and PHPUnit, `package.json`, `vite.config.js`, `phpunit.xml`, `bootstrap/app.php`, `config/database.php` (default connection), `config/filesystems.php`, `.env.example`, `README.md`
- All route files
- Models `User`, `SupportTicket` (attachment URL and reply visibility), and the relationship methods used above
- Migrations listed in section 8
- Middleware, `UserRole`, `UserRoleService`, `SupportTicketPolicy`, `AppServiceProvider`
- Controllers for auth sessions, registration, equipment index, reservations, payments (store path), support tickets (store/show), service providers, service requests, admin providers, admin rentals, admin settings
- Form requests named in section 10, including rule arrays
- Navbar route list, frontend layout head, auth login/register color classes, `app.css` base input styles
- Test method names
- Confirmation that `frontend.auth` views are not referenced

Not inspected line by line:

- Every Blade template's markup (114 view files exist)
- Full admin payment refund and transaction update methods
- `UpdateEquipmentRequest` versus store rules
- Seeder row contents other than `DatabaseSeeder` and `AdminUserSeeder`
- Factories beyond what controllers require
- CSS beyond `resources/css/app.css` and the classes cited above
- A live browser pass and a fresh `php artisan test` run at the time of writing
- The reported ticket-image HTTP 403 (see limitation 12)
- Contents of a local `.env` (intentionally unread)
