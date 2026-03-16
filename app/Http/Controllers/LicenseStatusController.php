<?php

namespace App\Http\Controllers;

use App\Services\LicenseManagerService;
use Illuminate\Http\Request;

class LicenseStatusController extends Controller
{
    protected LicenseManagerService $licenseService;

    public function __construct(LicenseManagerService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Display license status page.
     */
    public function status()
    {
        $licenseInfo = $this->licenseService->getLicenseInfo();
        
        return view('license.status', [
            'license' => $licenseInfo,
        ]);
    }

    /**
     * Display license error page.
     */
    public function error()
    {
        return view('license.error');
    }

    /**
     * Force refresh license from server.
     */
    public function refresh()
    {
        $this->licenseService->clearCache();
        $licenseInfo = $this->licenseService->verifyLicense(true);

        if ($licenseInfo && ($licenseInfo['valid'] ?? false)) {
            return redirect()->route('license.status')
                ->with('success', 'License refreshed successfully.');
        }

        return redirect()->route('license.error')
            ->with('error', 'Failed to refresh license. Please check your connection.');
    }

    /**
     * API endpoint for license status (for AJAX calls).
     */
    public function apiStatus()
    {
        $licenseInfo = $this->licenseService->getLicenseInfo();
        
        return response()->json($licenseInfo);
    }

    /**
     * Request offline activation.
     */
    public function requestOfflineActivation()
    {
        $result = $this->licenseService->requestOfflineActivation();

        if ($result) {
            return view('license.offline-activation', [
                'code' => $result['code'] ?? null,
                'signature' => $result['signature'] ?? null,
                'hardware_id' => $this->licenseService->generateHardwareId(),
            ]);
        }

        return redirect()->route('license.status')
            ->with('error', 'Failed to request offline activation.');
    }
}
