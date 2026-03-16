<?php

namespace App\Http\Middleware;

use App\Services\LicenseManagerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateLicenseManager
{
    protected LicenseManagerService $licenseService;

    public function __construct(LicenseManagerService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if license enforcement is disabled
        if (!config('license.enabled', true)) {
            return $next($request);
        }

        // Skip for excluded routes
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        // Check if license is valid
        if (!$this->licenseService->isLicenseValid()) {
            // Redirect to license error page or show error
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Invalid or expired license. Please contact your administrator.',
                    'license_status' => 'invalid',
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()->route('license.error')
                ->with('error', 'Your license is invalid or has expired. Please contact support.');
        }

        // Get license info and attach to request
        $licenseInfo = $this->licenseService->getLicenseInfo();
        $request->attributes->set('license', $licenseInfo);

        // Check if license is expiring soon (warning only, don't block)
        $daysRemaining = $licenseInfo['days_remaining'] ?? null;
        if ($daysRemaining !== null && $daysRemaining > 0 && $daysRemaining <= 30) {
            session()->flash('license_warning', "Your license will expire in {$daysRemaining} days. Please renew soon.");
        }

        return $next($request);
    }

    /**
     * Determine if the request should skip license check.
     */
    protected function shouldSkip(Request $request): bool
    {
        $excludeRoutes = config('license.exclude_routes', []);
        
        foreach ($excludeRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
