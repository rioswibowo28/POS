# License Manager Integration for POS Resto

## 🔗 Integration Guide

This document explains how to integrate POS Resto with the License Manager system.

## 📋 Prerequisites

1. License Manager server running (http://localhost:8000)
2. Valid license key from License Manager
3. POS Resto application

## ⚙️ Configuration

### 1. Environment Variables

Add these variables to your `.env` file:

```env
# License Manager Configuration
LICENSE_SERVER_URL=http://localhost:8000
LICENSE_KEY=YOUR-LICENSE-KEY-HERE

# License check interval (in seconds) - default 24 hours
LICENSE_CHECK_INTERVAL=86400

# Grace period after expiry (in days)
LICENSE_GRACE_PERIOD=7

# Enable/disable license enforcement
LICENSE_ENFORCEMENT_ENABLED=true

# Auto-check license on every request (false for better performance)
LICENSE_AUTO_CHECK=false

# Offline mode (uses cached/signed token)
LICENSE_OFFLINE_MODE=false
LICENSE_OFFLINE_TOKEN=

# Hardware ID components (for license binding)
LICENSE_HW_MAC=
LICENSE_HW_CPU=
LICENSE_HW_DISK=

# Notification email for expiry warnings
LICENSE_NOTIFY_EMAIL=admin@yourcompany.com
```

### 2. Register Middleware

Add to `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'license.check' => \App\Http\Middleware\ValidateLicenseManager::class,
    ]);
    
    // Optional: Apply globally to all web routes
    // $middleware->web(append: [
    //     \App\Http\Middleware\ValidateLicenseManager::class,
    // ]);
})
```

### 3. Add Routes

Add to `routes/web.php`:

```php
use App\Http\Controllers\LicenseStatusController;

// License routes (accessible without license check)
Route::get('/license-status', [LicenseStatusController::class, 'status'])
    ->name('license.status');
Route::get('/license-error', [LicenseStatusController::class, 'error'])
    ->name('license.error');
Route::get('/license-refresh', [LicenseStatusController::class, 'refresh'])
    ->name('license.refresh');
Route::get('/license-offline', [LicenseStatusController::class, 'requestOfflineActivation'])
    ->name('license.offline-activation');

// API endpoint for AJAX
Route::get('/api/license-status', [LicenseStatusController::class, 'apiStatus'])
    ->name('api.license.status');

// Protected routes (require valid license)
Route::middleware(['auth', 'license.check'])->group(function () {
    // Your POS routes here
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // ... other routes
});
```

## 🚀 Usage

### Command Line

Check license status from terminal:

```bash
# Check license status
php artisan license:check

# Force refresh from server
php artisan license:check --force

# Clear cached license data
php artisan license:check --clear-cache
```

### In Controllers

Access license information:

```php
public function index(Request $request)
{
    // Get license info attached by middleware
    $license = $request->attributes->get('license');
    
    $maxUsers = $license['max_users'] ?? null;
    $daysRemaining = $license['days_remaining'] ?? null;
    
    return view('dashboard', compact('license'));
}
```

### In Blade Templates

Display license warnings:

```blade
@if(session('license_warning'))
    <div class="alert alert-warning">
        {{ session('license_warning') }}
    </div>
@endif
```

Display license info:

```blade
@inject('licenseService', 'App\Services\LicenseManagerService')
@php
    $license = $licenseService->getLicenseInfo();
@endphp

@if($license['valid'])
    <div class="license-status">
        License expires: {{ $license['expiry_date'] ?? 'Never' }}
        ({{ $license['days_remaining'] ?? '∞' }} days)
    </div>
@endif
```

## 🔄 Periodic License Check

### Option 1: Laravel Scheduler (Recommended)

Add to `app/Console/Kernel.php` or `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('license:check --force')->daily();
```

Then run the scheduler:

```bash
php artisan schedule:work
```

Or add to cron (Linux):

```cron
* * * * * cd /path-to-pos-resto && php artisan schedule:run >> /dev/null 2>&1
```

### Option 2: Manual Check

Run periodically via task scheduler or cron:

```bash
php artisan license:check --force
```

## 🔒 Offline Mode

For environments without internet access:

### 1. Generate Signed Token

From License Manager admin:

```bash
# In LICENSE-MANAGER directory
POST /licenses/generate-token
{
    "license_key": "YOUR-LICENSE-KEY"
}
```

### 2. Configure POS Resto

Add to `.env`:

```env
LICENSE_OFFLINE_MODE=true
LICENSE_OFFLINE_TOKEN=eyJsaWNlbnNl...base64token
```

### 3. Hardware Binding (Optional)

Generate hardware ID:

```bash
php artisan tinker
>>> app(App\Services\LicenseManagerService::class)->generateHardwareId()
```

Request offline activation code from License Manager.

## 🧪 Testing

### Test License Verification

```bash
# Via browser
http://localhost/license-status

# Via API
curl http://localhost/api/license-status
```

### Test Middleware

Try accessing protected routes without valid license - should redirect to error page.

## 🐛 Troubleshooting

### License Check Fails

1. **Check connection to License Manager:**
   ```bash
   curl http://localhost:8000/api/license/verify -X POST \
     -H "Content-Type: application/json" \
     -d '{"license_key":"YOUR-KEY"}'
   ```

2. **Clear cache:**
   ```bash
   php artisan license:check --clear-cache
   php artisan cache:clear
   ```

3. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### License Key Not Found

- Verify `LICENSE_KEY` in `.env`
- Check License Manager dashboard for correct key
- Ensure license is activated

### Middleware Not Working

- Verify middleware is registered in `bootstrap/app.php`
- Check routes have `license.check` middleware applied
- Ensure `LICENSE_ENFORCEMENT_ENABLED=true`

## 📊 Monitoring

### View License Status

- **Web UI**: http://localhost/license-status
- **Command**: `php artisan license:check`
- **API**: `GET /api/license-status`

### Check Logs

All license-related events are logged:

```bash
# View recent license logs
tail -n 100 storage/logs/laravel.log | grep -i license
```

## 🔐 Security Best Practices

1. **Never commit** `.env` file with license keys
2. **Restrict access** to `/license-status` route (add auth middleware)
3. **Use HTTPS** for License Manager communication in production
4. **Rotate license keys** periodically
5. **Monitor** license usage and expiry dates

## 📞 Support

For license issues:

1. Check license status: http://localhost/license-status
2. Contact administrator
3. Check License Manager: http://localhost:8000/licenses

---

## 🎯 Quick Start Checklist

- [ ] Add `LICENSE_KEY` to `.env`
- [ ] Add `LICENSE_SERVER_URL` to `.env`
- [ ] Register middleware in `bootstrap/app.php`
- [ ] Add license routes to `routes/web.php`
- [ ] Apply `license.check` middleware to protected routes
- [ ] Test with: `php artisan license:check`
- [ ] Access: http://localhost/license-status
- [ ] Schedule daily license check (optional)

---

**Integration Complete! 🎉**

Your POS Resto is now protected by License Manager.
