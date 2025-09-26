# Mini Property Booking Platform
Laravel 12 (API) + React (Vite + Tailwind) — Token-based auth with Sanctum

Production-ready scaffold for a property booking app.

- Laravel 12 API (Sanctum personal access tokens)
- CRUD for Properties, Availability, and Bookings
- Role-based access (admin, guest)
- React 18 + Vite frontend
- Tailwind CSS (v3)
- **Swagger / OpenAPI docs (L5-Swagger) at `/api/documentation`**

---

## Requirements

- PHP 8.2+ · Composer 2.x
- MySQL 8+ (or MariaDB 10.6+) / SQLite
- Node 18+ and npm 9+
- Git

---

## 1) Backend Setup (Laravel 12)

```bash
cd property-booking
composer install

cp .env.example .env
php artisan key:generate

# Configure DB in .env (example):
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=booking
# DB_USERNAME=root
# DB_PASSWORD=secret

php artisan install:api
php artisan migrate --seed
php artisan serve
# API at http://localhost:8000/api
```

### .env essentials
```env
APP_NAME="MiniBooking"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking
DB_USERNAME=root
DB_PASSWORD=secret
```

### Token auth middleware (bootstrap/app.php)
```php
<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // DO NOT prepend EnsureFrontendRequestsAreStateful for API (token mode)
        $middleware->alias([
            'admin' => App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->create();
```

### Seeded accounts
- Admin: `admin@example.com` / `password`
- Guest: `guest@example.com` / `password`

---

## 2) Frontend Setup (React + Vite + Tailwind v3)

```bash
cd property-booking-FE
npm install
npm run dev
# App at http://localhost:5173
```

**tailwind.config.cjs**
```js
module.exports = {
  content: ["./index.html", "./src/**/*.{js,jsx}"],
  theme: { extend: {} },
  plugins: [],
};
```

**postcss.config.cjs**
```js
module.exports = {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
};
```

**src/index.css**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* helpers used by the UI */
.btn { @apply inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium shadow-sm transition; }
.btn-primary { @apply btn bg-blue-600 text-white hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-500; }
.btn-ghost { @apply btn bg-transparent text-slate-700 hover:bg-slate-100; }
.btn-danger { @apply btn bg-red-600 text-white hover:bg-red-700; }
.input { @apply block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30; }
.label { @apply block text-sm font-medium text-slate-700; }
.card { @apply rounded-xl border border-slate-200 bg-white shadow-sm; }
.card-body { @apply p-4 md:p-6; }
.card-title { @apply text-base font-semibold text-slate-900; }
.badge { @apply inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium; }
.badge-green { @apply badge bg-green-100 text-green-700; }
.badge-yellow { @apply badge bg-yellow-100 text-yellow-700; }
.badge-red { @apply badge bg-red-100 text-red-700; }

.shell { @apply min-h-screen bg-slate-50; }
.container-page { @apply mx-auto max-w-7xl px-4 md:px-6 lg:px-8; }
.section { @apply mt-6; }
```

**src/main.jsx**
```jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App.jsx';
import './index.css';

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
```

**API client (token mode) — src/api/http.js**
```js
import axios from 'axios';

const http = axios.create({
  baseURL: 'http://localhost:8000/api',
  withCredentials: false,
});

http.interceptors.request.use(cfg => {
  const t = localStorage.getItem('token');
  if (t) cfg.headers.Authorization = `Bearer ${t}`;
  return cfg;
});

export default http;
```

---

## 3) Swagger / OpenAPI Docs (L5-Swagger)

This repo includes OpenAPI annotations and L5-Swagger config for a ready-to-use docs site.

### Install
```bash
cd property-booking
composer require "darkaonline/l5-swagger"
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

### Configure `config/l5-swagger.php`
```php
return [
  'documentations' => [
    'default' => [
      'routes' => [
        'api'  => 'api/documentation',  // UI
        'docs' => 'api/docs',           // JSON
        'middleware' => [
          'api' => [], 'docs' => [], 'assets' => [], 'oauth2_callback' => [],
        ],
        'prefix' => '',
      ],
      'paths' => [
        'docs' => storage_path('api-docs'),
        'docs_json' => 'openapi.json',   // filename (Windows-safe)
        'docs_yaml' => false,
        'format_to_use_for_docs' => 'json',
        'annotations' => [
          base_path('app/Http/Controllers'),
          base_path('app/OpenApi'),
          base_path('app/Models'),
        ],
      ],
    ],
  ],
  'constants' => [
    'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', env('APP_URL', 'http://localhost:8000')),
  ],
];
```

### Add OpenAPI base + schemas
`app/OpenApi/OpenApi.php`:
```php
<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Mini Property Booking API",
 *   description="Laravel 12 + Sanctum (token) API for properties, availability, and bookings."
 * )
 * @OA\Server(url=L5_SWAGGER_CONST_HOST, description="Local API")
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="Token"
 * )
 */
class OpenApi {}
```

`app/OpenApi/Schemas.php` (sample):
```php
<?php

namespace App\OpenApi;

/**
 * @OA\Schema(schema="Property", type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="user_id", type="integer", example=1),
 *   @OA\Property(property="title", type="string", example="Cozy Studio"),
 *   @OA\Property(property="description", type="string"),
 *   @OA\Property(property="price_per_night", type="number", format="float", example=89.99),
 *   @OA\Property(property="location", type="string", example="Dubai Marina"),
 *   @OA\Property(property="amenities", type="array", @OA\Items(type="string")),
 *   @OA\Property(property="images", type="array", @OA\Items(type="string")),
 * )
 *
 * @OA\Schema(schema="Availability", type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="property_id", type="integer"),
 *   @OA\Property(property="start_date", type="string", format="date"),
 *   @OA\Property(property="end_date", type="string", format="date"),
 * )
 *
 * @OA\Schema(schema="Booking", type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="property_id", type="integer"),
 *   @OA\Property(property="guest_id", type="integer"),
 *   @OA\Property(property="start_date", type="string", format="date"),
 *   @OA\Property(property="end_date", type="string", format="date"),
 *   @OA\Property(property="status", type="string", enum={"pending","confirmed","rejected"}),
 * )
 *
 * @OA\Schema(schema="LoginRequest", required={"email","password"},
 *   @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
 *   @OA\Property(property="password", type="string", example="password"),
 * )
 *
 * @OA\Schema(schema="LoginResponse",
 *   @OA\Property(property="token", type="string", example="1|abcdef..."),
 *   @OA\Property(property="user", type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="role", type="string", enum={"admin","guest"})
 *   )
 * )
 */
class Schemas {}
```

### Generate & open docs
```bash
php artisan config:clear
php artisan l5-swagger:generate
php artisan route:list | findstr /I "documentation docs swagger"
```
Open:
- UI: http://localhost:8000/api/documentation
- JSON: http://localhost:8000/api/docs  (served from storage/api-docs/openapi.json)

Click **Authorize** in Swagger UI and use: `Bearer <token>`.

---

## 4) Tests

We include Feature and Unit tests with strong coverage for availability and booking overlap rules.

Run all tests
```bash
php artisan test
```
## 5) Run both apps

**Backend**
```bash
cd property-booking
php artisan serve
```

**Frontend**
```bash
cd property-booking-FE
npm run dev
```

---

## 6) Build

**Frontend**
```bash
cd property-booking-FE
npm run build
npm run preview
```

**Backend**
- Serve `public/` with your web server
- `php artisan migrate --force`

---

## 7) API summary

```
POST   /api/login                 -> { token, user }
POST   /api/logout                -> { message }
GET    /api/me                    -> user

GET    /api/properties
GET    /api/properties/{id}

# Admin
POST   /api/properties
PUT    /api/properties/{id}
DELETE /api/properties/{id}

GET    /api/properties/{id}/availabilities
POST   /api/properties/{id}/availabilities
DELETE /api/properties/{id}/availabilities/{availabilityId}

GET    /api/bookings
PUT    /api/bookings/{id}/status

# Guest
POST   /api/bookings
GET    /api/my-bookings

```

---

## 8) Troubleshooting

- **CSRF token mismatch**: You used cookie mode. Stay in token mode (no `EnsureFrontendRequestsAreStateful`, no `/sanctum/csrf-cookie`).
- **Tailwind not loading**: Ensure v3 config files; `index.css` imported in `main.jsx`; restart dev server.
- **Swagger 404**: Verify routes in `config/l5-swagger.php`, then `php artisan route:list`.
- **APP_URL in annotations**: Use `L5_SWAGGER_CONST_HOST` and reference it in `@OA\Server`.
- **Windows path error**: Use a filename for `docs_json` and ensure `storage/api-docs` exists.

## 👤 Author

**Haris Bin Zahid**  
Senior Software Engineer | PHP Laravel Expert
