<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenseService;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip license check for license routes and public assets
        $excludedRoutes = [
            'license.activate',
            'license.activate.process',
        ];

        if ($request->routeIs($excludedRoutes)) {
            return $next($request);
        }

        // Check license (fast, from cache or database)
        $result = $this->licenseService->check();

        if (!$result['valid']) {
            return redirect()->route('license.activate')
                ->with('error', $result['message']);
        }

        // Realtime sync on important routes (login, dashboard)
        $criticalRoutes = ['login', 'dashboard', 'home'];
        $shouldSyncNow = $request->routeIs($criticalRoutes) || $request->is('/');
        
        if ($shouldSyncNow) {
            // Perform immediate sync to check if license still valid on server
            try {
                $syncResult = $this->licenseService->syncWithServer();
                
                if ($syncResult['action'] === 'deleted') {
                    return redirect()->route('license.activate')
                        ->with('error', $syncResult['message']);
                }
            } catch (\Exception $e) {
                \Log::warning('Realtime license sync failed: ' . $e->getMessage());
                // Continue even if sync fails (network issue)
            }
        }

        // Background sync every 6 hours for other routes
        $lastSync = cache('license_last_sync');
        $shouldSync = !$lastSync || now()->diffInHours($lastSync) >= 6;
        
        if ($shouldSync && !$shouldSyncNow) {
            // Mark as syncing to prevent multiple concurrent syncs
            if (!cache('license_syncing')) {
                cache(['license_syncing' => true], 300); // 5 minutes lock
                cache(['license_last_sync' => now()], 21600); // 6 hours
                
                // Perform sync in background after response is sent
                app()->terminating(function () {
                    try {
                        $service = app(LicenseService::class);
                        $service->syncWithServer();
                    } catch (\Exception $e) {
                        \Log::warning('Background license sync failed: ' . $e->getMessage());
                    } finally {
                        cache()->forget('license_syncing');
                    }
                });
            }
        }

        // Show warning if license is expiring soon
        if ($result['expiring_soon']) {
            session()->flash('warning', sprintf(
                'License Anda akan kadaluarsa dalam %d hari. Silakan perpanjang license Anda.',
                $result['days_remaining']
            ));
        }

        return $next($request);
    }
}
