<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Show activation form (must connect to License Manager first)
     */
    public function showActivation()
    {
        $currentLicense = License::current();
        $connectionCheck = $this->licenseService->checkConnection();
        
        return view('license.activate', compact('currentLicense', 'connectionCheck'));
    }

    /**
     * Process activation (requires connection to License Manager)
     */
    public function activate(Request $request)
    {
        // Verify connection to License Manager first
        $connectionCheck = $this->licenseService->checkConnection();
        if (!$connectionCheck['connected']) {
            return back()
                ->withInput()
                ->with('error', 'Tidak dapat terhubung ke License Manager. Pastikan server License Manager aktif sebelum melakukan aktivasi.');
        }

        $request->validate([
            'license_key' => 'required|string',
            'customer_name' => 'required|string|max:255',
        ]);

        $result = $this->licenseService->activate(
            $request->license_key,
            $request->customer_name,
            $request->ip()
        );

        if ($result['success']) {
            return redirect()->route('dashboard')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Lookup license info from License Manager (proxy for AJAX)
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $serverUrl = \license_config('server_url', 'http://localhost:8000');
        $apiUrl = rtrim($serverUrl, '/') . '/api/license/lookup';

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(60)->post($apiUrl, [
                'license_key' => $request->license_key,
            ]);

            $body = trim($response->body(), "\xEF\xBB\xBF");
            $data = json_decode($body, true);

            return response()->json($data ?? ['found' => false], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'found' => false,
                'message' => 'Tidak dapat terhubung ke License Manager.',
            ], 503);
        }
    }

    /**
     * Show current license info
     */
    public function info()
    {
        $result = $this->licenseService->check();
        
        if (!$result['valid']) {
            return redirect()->route('license.activate')
                ->with('error', $result['message']);
        }

        $license = $result['license'];
        
        return view('license.info', compact('license'));
    }

    /**
     * Update/renew license from License Manager
     */
    public function updateLicense(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $result = $this->licenseService->renewFromServer(
            $request->license_key,
            $request->ip()
        );

        if ($result['success']) {
            return redirect()->route('license.info')
                ->with('success', $result['message']);
        }

        return back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Admin: List all licenses
     */
    public function index()
    {
        $licenses = License::withTrashed()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = $this->licenseService->getStats();

        return view('license.index', compact('licenses', 'stats'));
    }

    /**
     * Admin: Show create form
     */
    public function create()
    {
        return view('license.create');
    }

    /**
     * Admin: Store new license
     */
    public function store(Request $request)
    {
        $request->validate([
            'license_type' => 'required|in:trial,monthly,yearly,lifetime',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
            'max_users' => 'nullable|integer|min:1',
            'max_tables' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $license = $this->licenseService->create($request->all());

        return redirect()->route('license.show', $license->id)
            ->with('success', 'License berhasil dibuat! License Key: ' . $license->license_key);
    }

    /**
     * Admin: Show license details
     */
    public function show(License $license)
    {
        return view('license.show', compact('license'));
    }

    /**
     * Admin: Suspend license
     */
    public function suspend(License $license)
    {
        $license->update(['status' => 'suspended']);

        return back()->with('success', 'License berhasil di-suspend.');
    }

    /**
     * Admin: Renew license
     */
    public function renew(Request $request, License $license)
    {
        $request->validate([
            'license_type' => 'required|in:monthly,yearly,lifetime',
        ]);

        $this->licenseService->renew($license, $request->license_type);

        return back()->with('success', 'License berhasil diperpanjang.');
    }

    /**
     * Admin: Delete license
     */
    public function destroy(License $license)
    {
        $license->delete();

        return redirect()->route('license.index')
            ->with('success', 'License berhasil dihapus.');
    }
}
