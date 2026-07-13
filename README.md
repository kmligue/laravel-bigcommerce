# Laravel BigCommerce Package

A comprehensive Laravel package for BigCommerce integration with multi-tenancy support, subscription management, and advanced monitoring capabilities.

## 🚀 Features

- **BigCommerce API Integration** - Complete API client with authentication
- **Multi-tenancy Support** - Isolated database per store with automatic table creation
- **Subscription Management** - Stripe integration with plan-based access control
- **Webhook Processing** - Automated webhook handling and processing
- **Laravel Telescope Integration** - Comprehensive debugging and monitoring
- **Advanced User Management** - Role-based access control and trial management
- **Mail Notifications** - Automated email notifications for various events

## 📦 Installation

### 1. Add Repository to Composer

Add this to your `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/kmligue/laravel-bigcommerce"
    }
]
```

### 2. Install Package

```bash
composer require limonlabs/bigcommerce:dev-multitenancy2
```

### 3. Publish Configuration and Migrations

```bash
# Publish all package files
php artisan vendor:publish --tag=limonlabs-bigcommerce-config
php artisan vendor:publish --tag=limonlabs-bigcommerce-migrations

# Or publish everything at once
php artisan vendor:publish
# Then select "Limonlabs\Bigcommerce\Providers\LimonlabsBigcommerceProvider"
```

### 4. Configure Session Middleware

For Laravel 11, add the StartSession middleware to your `bootstrap/app.php`:

```php
use Illuminate\Session\Middleware\StartSession;

->withMiddleware(function (Middleware $middleware) {
    $middleware->append(StartSession::class);
})
```

### 5. Set Environment Variables

```env
# Session configuration (required for cross-origin frontend)
SESSION_DRIVER=file
SESSION_SAME_SITE=none
SESSION_SECURE_COOKIE=true

# Frontend SPA URL (required — must exactly match your deployed frontend origin)
BIGCOMMERCE_FRONTEND_URL=https://bigcommercefrontendreact.netlify.app

# Optional extra allowed CORS origins (comma-separated), e.g. preview deploys
# BIGCOMMERCE_FRONTEND_ALLOWED_ORIGINS=https://deploy-preview-123.netlify.app

# BigCommerce API credentials
BC_APP_ID=your_app_id
BC_LOCAL_CLIENT_ID=your_local_client_id
BC_APP_CLIENT_ID=your_app_client_id
BC_LOCAL_SECRET=your_local_secret
BC_APP_SECRET=your_app_secret
BC_LOCAL_ACCESS_TOKEN=your_local_access_token
BC_LOCAL_STORE_HASH=your_local_store_hash

# Stripe configuration
STRIPE_KEY=pk_test_your_stripe_key
STRIPE_SECRET=sk_test_your_stripe_secret
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# Plan-specific Stripe price IDs
STRIPE_BRONZE_PLAN_ID=price_your_bronze_plan
STRIPE_SILVER_PLAN_ID=price_your_silver_plan
STRIPE_GOLD_PLAN_ID=price_your_gold_plan

# Mail configuration
MAIL_FROM_ADDRESS=support@limonlabs.dev
MAIL_FROM_NAME="[STAGING] Limon Labs Support"
MAIL_SUBJECT_PREFIX="[STAGING]"
ADMIN_MAIL_FROM_ADDRESS=dev@limonlabs.dev,support@limonlabs.dev

# Help form (StaticForms)
STATICFORMS_ACCESS_KEY=your_staticforms_key
```

## React Frontend

Store and admin UI is a **standalone React SPA** in the `frontend/` folder, deployed separately from Laravel. Laravel serves only the API, auth callbacks (`/auth/load`, `/auth/install`), and Blade templates for emails, errors, and maintenance.

### Architecture

- **Laravel (API domain):** `https://api.yourapp.com` — BigCommerce Load URL, JSON API, sessions
- **Frontend (app domain):** `https://app.yourapp.com` — React SPA, static assets

After `/auth/load` creates a session on the API domain, Laravel redirects to the frontend URL. The React app calls the API cross-origin with cookies and CSRF.

### Frontend environment (`frontend/.env`)

```env
VITE_API_URL=https://api.yourapp.com
VITE_APP_NAME="Your App"
VITE_STRIPE_KEY=pk_test_your_stripe_key
```

Copy from `frontend/.env.example` and adjust values.

### Building for production

```bash
cd packages/limonlabs/bigcommerce/frontend   # or vendor/limonlabs/bigcommerce/frontend
npm install
npm run build
```

Deploy the `frontend/dist/` directory to your static host (S3 + CloudFront, Netlify, Vercel, nginx, etc.). Configure the host to rewrite all paths (`/stores/*`, `/limonadmin/*`) to `index.html`.

**nginx example:**

```nginx
location / {
    try_files $uri $uri/ /index.html;
}
```

### Local development

```bash
# Terminal 1 — Laravel API
php artisan serve --port=8000

# Terminal 2 — Frontend dev server
cd packages/limonlabs/bigcommerce/frontend
cp .env.example .env
# Set VITE_API_URL=http://localhost:8000
npm install
npm run dev
```

Set `BIGCOMMERCE_FRONTEND_URL=http://localhost:5173` in the Laravel `.env` so post-auth redirects land on the Vite dev server.

### BigCommerce app URLs

| Setting | Value |
|---------|-------|
| Load URL | `https://api.yourapp.com/auth/load` (Laravel domain) |
| Auth callback | `https://api.yourapp.com/auth/install` |

Users land on the frontend after auth; do not point the Load URL at the frontend domain.

### Cross-origin cookies (iframe)

The app runs inside the BigCommerce admin iframe. Cross-origin API calls require:

- `SESSION_SAME_SITE=none` and `SESSION_SECURE_COOKIE=true` on Laravel
- HTTPS on both API and frontend domains in production
- `BIGCOMMERCE_FRONTEND_URL` must **exactly** match the browser origin of your deployed SPA (scheme + host, no trailing slash). Example: if Netlify serves `https://bigcommercefrontendreact.netlify.app`, use that — not a placeholder like `https://app.yourapp.com`.
- After changing CORS or session env vars, run `php artisan config:clear` on the API server
- Test in Chrome with third-party cookie restrictions enabled

### JSON API endpoints

All API routes use the `web` middleware group (session + CSRF). Call `GET /api/csrf-cookie` before the first POST — it returns `{ "csrf_token": "..." }` in the JSON body (the frontend cannot read API-domain cookies cross-origin). Store routes use `bigcommerce.store.auth`; most also require `welcome.auth`.

**Store**

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/stores/{storeHash}/context` | Store layout data, plan status, trial notice |
| GET | `/api/stores/{storeHash}/settings` | Get all settings, or subset via `?keys=key1,key2` |
| POST | `/api/stores/{storeHash}/settings` | Replace store settings with request JSON |
| POST | `/api/stores/{storeHash}/welcome` | Complete onboarding |
| POST | `/api/stores/{storeHash}/help` | Submit help form |
| GET | `/api/stores/{storeHash}/billing` | Pricing plans data |
| GET | `/api/stores/{storeHash}/billing/history` | Invoice history |
| GET | `/api/stores/{storeHash}/billing/{plan}/setup-intent` | Stripe setup intent for card entry |
| POST | `/api/stores/{storeHash}/billing/cancel` | Cancel subscription |
| POST | `/api/stores/{storeHash}/billing/trial/change` | Update trial end date |
| POST | `/api/stores/{storeHash}/billing/{plan}/select` | Select or change plan |
| POST | `/api/stores/{storeHash}/billing/{plan}` | Create subscription with payment method |

**Admin**

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/limonadmin/login` | Admin login |
| GET | `/api/limonadmin/installs` | List installed stores |
| POST | `/api/limonadmin/unified-billing/create` | Create unified billing checkout |

### BigCommerce API proxy (`/bc-api`)

The package exposes a generic passthrough for the BigCommerce v2/v3 REST APIs, so the frontend can look up store data (products, orders, customers, etc.) without shipping the store's access token to the browser:

```
ANY /bc-api/{endpoint}   where {endpoint} matches v2/* or v3/*
```

Handled by `BigcommerceController@proxyBigCommerceAPIRequest`. The request is forwarded to `https://api.bigcommerce.com/{store_hash}/{endpoint}` with the app's `X-Auth-Client` and the store's `X-Auth-Token` headers injected server-side. BigCommerce's response body and status code are returned unchanged with a `application/json` content type.

**Examples**

```
GET /bc-api/v3/catalog/products     → GET https://api.bigcommerce.com/{store_hash}/v3/catalog/products
GET /bc-api/v2/store                → GET https://api.bigcommerce.com/{store_hash}/v2/store.json
PUT /bc-api/v3/catalog/products/42  → PUT with the request body forwarded
```

**Authentication**

- **Production:** the store hash and access token are resolved from the session created by `GET /auth/load`, so the proxy only works for a browser that has an active store session (`user_id`/`store_hash` in session). There is no separate auth middleware on the route.
- **Local:** `BC_LOCAL_STORE_HASH`, `BC_LOCAL_ACCESS_TOKEN`, and `BC_LOCAL_CLIENT_ID` from `.env` are used instead, so any local request works without a session.

**Behavior and limitations**

- v2 endpoints automatically get a `.json` suffix appended to normalize responses against the v3 API.
- The request body is only forwarded for `PUT` requests; `POST` bodies are not passed through.
- The query string is **not** forwarded to BigCommerce — only the path is proxied, so filters like `?limit=10` have no effect.
- Only paths starting with `v2/` or `v3/` are accepted; anything else 404s.

## 🔧 Configuration

### Default StoreInfo Model

The package provides a default `StoreInfo` model with the following structure:

```php
<?php

namespace Limonlabs\Bigcommerce\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreInfo extends Authenticatable
{
    use Billable, SoftDeletes;

    protected $table = 'store_info';

    protected $fillable = [
        'store_hash',
        'access_token',
        'user_id',
        'name',
        'first_name',
        'last_name',
        'user_email',
        'timezone',
        'secure_url',
        'status',
        'country',
        'plan_level',
        'multi_storefront_enabled',
        'internal_settings',
        'settings',
        'trial_ends_at',
        'has_advanced_during_trial',
        'post_trial_plan'
    ];

    protected $casts = [
        'settings' => 'array',
        'internal_settings' => 'array',
        'trial_ends_at' => 'datetime'
    ];

    // Relationships
    public function webhooks() {
        return $this->hasMany(\Limonlabs\Bigcommerce\Models\Webhook::class, 'store_id');
    }

    // Plan management methods
    public function getPlanAttribute() { /* ... */ }
    public function getChannelsAttribute() { /* ... */ }
    public function getPlanStatus() { /* ... */ }
    public function hasPlanAccess($planKey) { /* ... */ }
    public function getAccessibleFeatures() { /* ... */ }
}
```

### Customizing the Model

You can override the default model in `config/tenant.php`:

```php
'tenant' => \App\Models\YourCustomStoreInfo::class,
```

### Tenant Tables

All tenant-specific tables should use the `TenantConnection` trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Limonlabs\Bigcommerce\Database\Traits\TenantConnection;

class Feedback extends Model
{
    use TenantConnection;

    protected $table = 'feedbacks';

    protected $fillable = [
        'store_id',
        'name',
        'email',
        'message',
        'page_location',
        'user_agent',
        'sentiment'
    ];
}
```

## 🔍 Laravel Telescope Integration

This package includes comprehensive Laravel Telescope integration for debugging and monitoring BigCommerce operations.

### Quick Installation

```bash
# Install Laravel Telescope first
composer require laravel/telescope

# Install BigCommerce Telescope integration
php artisan bigcommerce:install-telescope
```

### What It Monitors

- **BigCommerce API Operations**: All API requests, responses, and errors
- **Database Operations**: Comprehensive query monitoring and performance tracking
- **Webhook Processing**: Webhook reception, processing, and failures
- **Subscription Management**: Plan changes, cancellations, and billing events
- **Store Lifecycle**: Installation, uninstallation, and configuration changes

### Configuration

Add to your `.env` file:

```env
# Enable Telescope
TELESCOPE_ENABLED=true
BIGCOMMERCE_ENABLE_TELESCOPE=true

# BigCommerce specific watchers
TELESCOPE_BIGCOMMERCE_WATCHER=true
TELESCOPE_BIGCOMMERCE_DB_WATCHER=true

# Database monitoring
TELESCOPE_LOG_ALL_QUERIES=true
TELESCOPE_LOG_SLOW_QUERIES=true
TELESCOPE_QUERY_SLOW=100
```

### Access Dashboard

Visit `/telescope` in your browser to access the monitoring dashboard.

### Documentation

For detailed Telescope integration documentation, see [TELESCOPE_README.md](TELESCOPE_README.md).

## 🗄️ Database Management

### Automatic Tenant Table Creation

When a new store is created, the package automatically:

1. Creates a unique database prefix for the store
2. Runs tenant-specific migrations
3. Sets up isolated table structure

### Cleanup Old Tenant Tables

Delete tenant tables that are over 30 days old:

```bash
php artisan delete:old-tenant-tables
```

Add to your cron job:

```bash
php artisan schedule:run >> /dev/null 2>&1
```

## 📧 Mail Configuration

The package includes automated email notifications:

- **App Installation/Uninstallation** - Notify admins of store changes
- **Help Requests** - Process and forward user support requests
- **Subscription Changes** - Notify users of plan modifications

### Mail Settings

```env
MAIL_FROM_ADDRESS=support@limonlabs.dev
MAIL_FROM_NAME="[STAGING] Limon Labs Support"
MAIL_SUBJECT_PREFIX="[STAGING]"
ADMIN_MAIL_FROM_ADDRESS=dev@limonlabs.dev,support@limonlabs.dev
```

**Note**: 
- Use `[STAGING]` prefix for development environments
- Remove prefix for production
- Default admin emails are `dev@limonlabs.dev` and `support@limonlabs.dev`

## 🎨 Publishing Assets

### Images and Logos

```bash
php artisan vendor:publish --tag=limonlabs-bigcommerce-images
```

## 🔐 Security Features

- **API Key Protection** - Automatic redaction of sensitive data in logs
- **Multi-tenant Isolation** - Complete database separation between stores
- **Access Control** - Plan-based feature access with trial management
- **Secure Webhooks** - Validated webhook processing with signature verification

## 🚀 Advanced Features

### Trial Management

- **Advanced Trial Access** - Grant premium features during trial periods
- **Post-trial Plans** - Specify default plan after trial expiration
- **Flexible Trial Extensions** - Customizable trial periods and conditions

### Plan-based Access Control

```php
// Check if user has access to specific plan features
if ($storeInfo->hasPlanAccess('gold')) {
    // Enable premium features
}

// Get all accessible features
$features = $storeInfo->getAccessibleFeatures();
```

### BigCommerce API Integration

```php
// Get store channels
$channels = $storeInfo->channels;

// Access BigCommerce API client
$client = app(\Limonlabs\Bigcommerce\Libraries\Bigcommerce\BcClient::class);
```

## 🛠️ Troubleshooting

### Common Issues

1. **Session Store Not Set**
   - Ensure `StartSession` middleware is configured
   - Set `SESSION_DRIVER=file` in `.env`

2. **Tenant Tables Not Created**
   - Check database permissions
   - Verify migration files are published
   - Run `php artisan migrate:status` to check migration status

3. **Telescope Not Working**
   - Verify `TELESCOPE_ENABLED=true`
   - Check `BIGCOMMERCE_ENABLE_TELESCOPE=true`
   - Ensure Laravel Telescope is installed

4. **CORS errors (`Access-Control-Allow-Origin` must not be `*`)**
   - Set `BIGCOMMERCE_FRONTEND_URL` to your real frontend origin (e.g. `https://bigcommercefrontendreact.netlify.app`)
   - Run `php artisan config:clear` after env changes
   - Ensure `VITE_API_URL` on the frontend points at the Laravel API domain
   - Set `VITE_STRIPE_KEY` on the frontend host (Netlify env vars) to fix Stripe errors

### Debug Commands

```bash
# Check package status
php artisan bigcommerce:status

# Clear package cache
php artisan config:clear
php artisan cache:clear

# Check Telescope status
php artisan telescope:status
```

## 📚 Additional Resources

- [Laravel Telescope Documentation](https://laravel.com/docs/telescope)
- [BigCommerce API Documentation](https://developer.bigcommerce.com/)
- [Laravel Package Development](https://laravel.com/docs/packages)
- [Package Repository](https://github.com/kmligue/laravel-bigcommerce)

## 🤝 Support

For issues and support:

1. Check the troubleshooting section above
2. Review package configuration
3. Check environment variables
4. Contact package maintainers

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).
