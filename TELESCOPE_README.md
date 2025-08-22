# Laravel Telescope Integration for BigCommerce Package

This document explains how to integrate and use Laravel Telescope with the LimonLabs BigCommerce package for comprehensive debugging and monitoring.

## 🚀 Quick Start

### **1. Install Telescope Integration**
```bash
php artisan bigcommerce:install-telescope
```

### **2. Access Telescope Dashboard**
- **URL**: `/telescope`
- **Access**: Automatically secured
- **Local Development**: Always accessible
- **Production**: Requires LimonAdmin authentication

### **3. Configuration (Automatic)**
The command automatically sets up:
- ✅ Environment variables
- ✅ Security settings
- ✅ HTTP Client monitoring
- ✅ Database monitoring
- ✅ BigCommerce-specific watchers

### **4. Ready to Use**
- **BigCommerce API calls** automatically monitored
- **Database operations** tracked with performance metrics
- **Security** implemented with multi-layer protection
- **LimonAdmin integration** seamless access

> **Note**: Laravel Telescope is automatically installed as a dependency - no manual installation required!

## ⚙️ Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# BigCommerce Telescope Integration - Master Control
BIGCOMMERCE_ENABLE_TELESCOPE=true

# Telescope Core Settings (Optional - defaults to BIGCOMMERCE_ENABLE_TELESCOPE)
# TELESCOPE_ENABLED=true  # Uncomment to override data collection behavior

# Telescope Path (optional)
TELESCOPE_PATH=telescope

# Telescope Storage
TELESCOPE_DRIVER=database

# BigCommerce Telescope Watchers
TELESCOPE_BIGCOMMERCE_WATCHER=true
TELESCOPE_BIGCOMMERCE_DB_WATCHER=true

# Database monitoring settings
TELESCOPE_LOG_ALL_QUERIES=true
TELESCOPE_LOG_SLOW_QUERIES=true

# HTTP Client monitoring (for BigCommerce API calls)
TELESCOPE_HTTP_CLIENT_WATCHER=true

# Telescope Security Settings
TELESCOPE_ALLOWED_IPS=127.0.0.1,::1
TELESCOPE_REQUIRE_AUTH=true
TELESCOPE_ALLOWED_ROLES=admin
TELESCOPE_ALLOW_LIMONADMIN=true

# Standard Telescope Watchers
TELESCOPE_QUERY_WATCHER=true
TELESCOPE_MODEL_WATCHER=true
TELESCOPE_REQUEST_WATCHER=true
TELESCOPE_MAIL_WATCHER=true
TELESCOPE_JOB_WATCHER=true
TELESCOPE_EXCEPTION_WATCHER=true

# Performance Settings
TELESCOPE_QUERY_SLOW=100
TELESCOPE_REQUEST_SIZE_LIMIT=64
```

### Simplified Configuration Approach

The BigCommerce package now uses a **single master control** for Telescope integration:

#### **Primary Control Variable:**
```env
BIGCOMMERCE_ENABLE_TELESCOPE=true    # Master switch - enables everything
```

#### **Optional Override:**
```env
TELESCOPE_ENABLED=false              # Override data collection (advanced use)
```

#### **Configuration Scenarios:**

| BIGCOMMERCE_ENABLE_TELESCOPE | TELESCOPE_ENABLED | Result |
|------------------------------|-------------------|---------|
| `true` | `true` (default) | ✅ Full Telescope functionality |
| `true` | `false` | ⚠️ UI accessible, no data collection |
| `false` | `true` | ❌ No Telescope (package not loaded) |
| `false` | `false` | ❌ No Telescope (package not loaded) |

### BigCommerce Package Configuration

The package configuration is located in `config/bigcommerce.php`:

```php
return [
    // Enable Telescope integration
    'enable_telescope' => env('BIGCOMMERCE_ENABLE_TELESCOPE', false),

    // Telescope specific settings
    'telescope' => [
        'enable_bigcommerce_watcher' => env('TELESCOPE_BIGCOMMERCE_WATCHER', true),
        'enable_bigcommerce_db_watcher' => env('TELESCOPE_BIGCOMMERCE_DB_WATCHER', true),
        'enable_query_watcher' => env('TELESCOPE_QUERY_WATCHER', true),
        'enable_model_watcher' => env('TELESCOPE_MODEL_WATCHER', true),
        'enable_request_watcher' => env('TELESCOPE_REQUEST_WATCHER', true),
        'enable_mail_watcher' => env('TELESCOPE_MAIL_WATCHER', true),
        'enable_job_watcher' => env('TELESCOPE_JOB_WATCHER', true),
        'enable_exception_watcher' => env('TELESCOPE_EXCEPTION_WATCHER', true),
        'log_all_queries' => env('TELESCOPE_LOG_ALL_QUERIES', true),
        'log_slow_queries' => env('TELESCOPE_LOG_SLOW_QUERIES', true),
        'query_slow_threshold' => env('TELESCOPE_QUERY_SLOW', 100),
        'request_size_limit' => env('TELESCOPE_REQUEST_SIZE_LIMIT', 64),
    ],
];
```

## 🔍 What Telescope Monitors

### **Simplified & Comprehensive Monitoring**

The BigCommerce package now uses a **streamlined approach** that eliminates redundancy:

- **❌ No more duplicate logging** - Model events and queries were redundant
- **✅ Single source of truth** - `QueryExecuted` event captures everything
- **🎯 Better performance** - Fewer event listeners, less overhead
- **🔍 Cleaner data** - Consistent structure across all database operations

### BigCommerce API Operations

- **API Requests**: All outgoing requests to BigCommerce APIs
- **API Responses**: Response data and status codes
- **Connection Failures**: Network errors and timeouts
- **Request/Response Headers**: API authentication and metadata

### Database Operations

- **All Database Queries**: Every SQL query executed (configurable)
- **Slow Query Detection**: Queries above performance threshold
- **Multi-tenant Context**: Automatic store hash detection from tenant databases
- **Query Performance**: Execution time, connection details, and database context
- **Security**: Automatic sanitization of sensitive data in query bindings

### Webhook Processing

- **Webhook Reception**: Incoming webhook data
- **Webhook Processing**: Processing time and status
- **Webhook Errors**: Failed webhook processing attempts

### Subscription Management

- **Subscription Creation**: New subscription events
- **Subscription Updates**: Plan changes and modifications
- **Subscription Cancellations**: Cancellation reasons and timing

### Store Lifecycle

- **Store Installation**: App installation events
- **Store Uninstallation**: App removal events
- **Store Configuration**: Settings and metadata changes

## 🎯 Accessing Telescope

### Dashboard URL

Access the Telescope dashboard at:

```
http://your-app.com/telescope
```

### User Access Control

Add this method to your User model to control access:

```php
// app/Models/User.php

use Laravel\Telescope\Telescope;

public static function canViewTelescope($request): bool
{
    // Only allow in development/staging
    if (!app()->environment(['local', 'staging'])) {
        return false;
    }

    // Only allow admin users
    return $request->user()->hasRole('admin');
}
```

## 📊 Understanding Telescope Data

### Entry Types

- **`bigcommerce-request`**: Outgoing API requests
- **`bigcommerce-response`**: API responses
- **`bigcommerce-error`**: Connection failures and errors
- **`bigcommerce-webhook`**: Webhook processing
- **`bigcommerce-subscription`**: Subscription changes
- **`bigcommerce-store`**: Store lifecycle events
- **`bigcommerce-db-query`**: All database queries executed
- **`bigcommerce-db-slow-query`**: Slow database queries detected

### Tags

Use these tags to filter and search:

- `bigcommerce` - All BigCommerce operations
- `api` - API requests and responses
- `webhook` - Webhook processing
- `subscription` - Subscription management
- `store` - Store lifecycle
- `database` - Database operations
- `query` - Database queries
- `slow-query` - Performance monitoring
- `performance` - Performance-related entries
- `error` - Errors and failures

### Content Structure

Each entry contains:

```json
{
    "name": "Human-readable description",
    "type": "Entry type identifier",
    "content": {
        "store_hash": "Store identifier",
        "url": "API endpoint (for requests)",
        "method": "HTTP method (for requests)",
        "status": "Response status (for responses)",
        "error": "Error message (for errors)",
        "timestamp": "ISO timestamp",
        "sql": "SQL query (for database operations)",
        "bindings": "Query parameters (sanitized)",
        "time": "Execution time in milliseconds",
        "connection": "Database connection name"
    },
    "tags": ["relevant", "tags"],
    "created_at": "Entry creation time"
}
```

## 🛡️ Security Considerations

### **Multi-Layer Authorization System**

The BigCommerce package implements a comprehensive security approach:

#### **1. Environment-Based Access**
- **Local/Development**: Always accessible for debugging
- **Production**: Requires proper authentication

#### **2. User Authentication**
- **BigCommerce Store Owners**: Automatic access
- **Admin Users**: Role-based access (`hasRole('admin')`)
- **Permission-Based**: Custom permissions (`can('view-telescope')`)
- **User ID 1**: Common admin pattern fallback

#### **3. LimonAdmin Integration**
- **Session-Based**: `session()->get('limonadmin') === true`
- **Configurable**: `TELESCOPE_ALLOW_LIMONADMIN=true/false`
- **Seamless Access**: When logged into LimonAdmin

#### **4. IP Restrictions**
- **Whitelist Approach**: `TELESCOPE_ALLOWED_IPS=127.0.0.1,::1`
- **Corporate Networks**: Support for IP ranges
- **Security**: Only specific IPs can access

#### **5. Default Security Stance**
- **Deny by Default**: Access denied unless explicitly allowed
- **No Open Access**: Production environments are secure by default

### **Sensitive Data Protection**

The package automatically redacts sensitive information:

- API keys and tokens
- Webhook secrets
- Stripe secrets
- Passwords and authentication data
- Personal identifiable information

### **Environment Restrictions**

Telescope is automatically controlled by the BigCommerce package:

```php
// config/telescope.php (automatically managed)
'enabled' => env('TELESCOPE_ENABLED', env('BIGCOMMERCE_ENABLE_TELESCOPE', false)),
```

### **Access Control**

The package automatically handles access control - no need to implement custom methods:

```php
// ✅ Automatic - No need to implement
// The package handles all authorization logic
```

## 🔧 Customization

### Custom Watchers

Create custom watchers for specific BigCommerce operations:

```php
// app/Watchers/CustomBigcommerceWatcher.php

use Limonlabs\Bigcommerce\Watchers\BigcommerceWatcher;

class CustomBigcommerceWatcher extends BigcommerceWatcher
{
    public function register($app)
    {
        parent::register($app);
        
        // Add custom event listeners
        Event::listen('custom.bigcommerce.event', function ($event) {
            $this->recordCustomEvent($event);
        });
    }
}
```

### Database Query Monitoring

The package automatically monitors all database queries:

```php
// All queries are captured automatically
// No need to add individual model event listeners

// Slow queries are automatically detected
// Configure threshold in .env: TELESCOPE_QUERY_SLOW=100
```

### Custom Tags

Add custom tags to categorize entries:

```php
// config/telescope.php
'tags' => [
    'custom-operation' => 'Custom BigCommerce Operation',
    'integration' => 'Third-party Integration',
    'performance' => 'Performance Monitoring',
],
```

## 📈 Performance Monitoring

### Query Performance

Monitor slow database queries:

```env
TELESCOPE_QUERY_SLOW=100  # Log queries slower than 100ms
TELESCOPE_LOG_ALL_QUERIES=true  # Log every database query
TELESCOPE_LOG_SLOW_QUERIES=true  # Log slow queries separately
```

### Request Size Limits

Control request logging size:

```env
TELESCOPE_REQUEST_SIZE_LIMIT=64  # Log requests up to 64KB
```

### Storage Management

Telescope data can grow quickly. Implement cleanup:

```bash
# Clear old entries
php artisan telescope:clear

# Prune old data
php artisan telescope:prune
```

## 🚨 Troubleshooting

### Common Issues

1. **Telescope Not Loading**
   - **Primary check**: Verify `BIGCOMMERCE_ENABLE_TELESCOPE=true`
   - **Secondary check**: Check if `TELESCOPE_ENABLED` is set to `false` (overrides master setting)
   - Ensure Telescope package is installed

2. **No BigCommerce Data**
   - Verify watchers are enabled in config
   - Check environment variables
   - Ensure BigCommerce operations are running
   - Verify `TELESCOPE_LOG_ALL_QUERIES=true` for database monitoring

3. **Performance Issues**
   - Reduce logging verbosity
   - Increase query slow threshold
   - Implement data pruning

4. **Database Errors**
   - Run Telescope migrations
   - Check database connection
   - Verify table permissions

### Debug Commands

```bash
# Check Telescope status
php artisan telescope:status

# Clear Telescope data
php artisan telescope:clear

# Prune old entries
php artisan telescope:prune

# Check BigCommerce configuration
php artisan config:show bigcommerce
```

## 📚 Advanced Usage

### Custom Event Dispatching

Dispatch custom events for monitoring:

```php
use Illuminate\Support\Facades\Event;

// Dispatch custom BigCommerce events
Event::dispatch('bigcommerce.custom.operation', [
    'store_hash' => $storeHash,
    'operation' => 'custom_operation',
    'data' => $operationData,
]);
```

### Integration with Other Tools

Telescope integrates with:

- **Laravel Horizon** - Queue monitoring
- **Laravel Debugbar** - Request debugging
- **Laravel Log Viewer** - Log file viewing
- **Custom Monitoring Tools** - Via Telescope API

### API Access

Access Telescope data programmatically:

```php
use Laravel\Telescope\Telescope;

// Get recent entries
$entries = Telescope::getEntries();

// Filter by type
$apiEntries = Telescope::getEntries(['type' => 'bigcommerce-request']);

// Filter by tags
$webhookEntries = Telescope::getEntries(['tags' => ['webhook']]);
```

## 🔄 Maintenance

### Regular Cleanup

Set up scheduled cleanup:

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Prune Telescope data daily
    $schedule->command('telescope:prune')->daily();
    
    // Clear old entries weekly
    $schedule->command('telescope:clear')->weekly();
}
```

### Data Retention

Configure data retention policies:

```env
# Keep entries for 7 days
TELESCOPE_ENTRY_RETENTION_DAYS=7

# Keep slow queries for 30 days
TELESCOPE_SLOW_QUERY_RETENTION_DAYS=30
```

## 📞 Support

For issues with Telescope integration:

1. Check the troubleshooting section above
2. Review Laravel Telescope documentation
3. Check package configuration
4. Review environment variables
5. Contact package maintainers

## 🔗 Useful Links

- [Laravel Telescope Documentation](https://laravel.com/docs/telescope)
- [BigCommerce API Documentation](https://developer.bigcommerce.com/)
- [Laravel Package Development](https://laravel.com/docs/packages)
- [Package Repository](https://github.com/limonlabs/laravel-bigcommerce)
