<?php

namespace App\Services;

use App\Models\License;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LicenseService
{
    /**
     * Check connection to License Manager server
     * Must be connected before activation is allowed
     */
    public function checkConnection(): array
    {
        $serverUrl = \license_config('server_url', 'http://localhost:8000');

        // Try multiple endpoints to verify License Manager is reachable
        $endpoints = [
            '/api/license/ping',
            '/api/license/verify',
        ];

        foreach ($endpoints as $endpoint) {
            $apiUrl = rtrim($serverUrl, '/') . $endpoint;

            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get($apiUrl);

                // Any response (even 4xx/5xx) means the server is reachable
                if ($response->status() < 500) {
                    return [
                        'connected' => true,
                        'server_url' => $serverUrl,
                        'message' => 'Terhubung ke License Manager.',
                    ];
                }
            } catch (\Exception $e) {
                // Try next endpoint
                continue;
            }
        }

        // Also try a simple GET to the server root
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($serverUrl);
            if ($response->status() < 500) {
                return [
                    'connected' => true,
                    'server_url' => $serverUrl,
                    'message' => 'Terhubung ke License Manager.',
                ];
            }
        } catch (\Exception $e) {
            // Server completely unreachable
        }

        \Log::warning('License Manager connection check failed', ['server_url' => $serverUrl]);

        return [
            'connected' => false,
            'server_url' => $serverUrl,
            'message' => 'Tidak dapat terhubung ke License Manager di ' . $serverUrl . '. Pastikan server License Manager sedang berjalan.',
        ];
    }

    /**
     * Activate a license by verifying with License Manager API
     */
    public function activate(string $licenseKey, string $customerName, string $ip): array
    {
        // Check if already activated locally (exclude soft-deleted)
        $existingLicense = License::where('license_key', $licenseKey)->first();
        if ($existingLicense && $existingLicense->activated_at) {
            return [
                'success' => false,
                'message' => 'License key sudah diaktifkan sebelumnya di aplikasi ini.'
            ];
        }

        // Verify with License Manager API
        $apiUrl = \license_config('server_url', 'http://localhost:8000') . '/api/license/activate';
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->post($apiUrl, [
                'license_key' => $licenseKey,
                'customer_name' => $customerName,
                'ip_address' => $ip,
                'application' => 'POS-RESTO',
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => $response->json('message') ?? 'Gagal memverifikasi license dengan server.'
                ];
            }

            // Parse response with BOM handling
            $body = trim($response->body(), "\xEF\xBB\xBF"); // Remove BOM
            $responseData = json_decode($body, true);
            
            if (!$responseData || !isset($responseData['data'])) {
                \Log::error('License activation error: Invalid API response', [
                    'raw' => substr($response->body(), 0, 200),
                    'parsed' => $responseData
                ]);
                return [
                    'success' => false,
                    'message' => 'Response dari License Manager tidak valid.'
                ];
            }
            
            $data = $responseData['data'];
            
            // Use expiry_date from License Manager (server is the source of truth)
            $startDate = !empty($data['activation_date']) ? Carbon::parse($data['activation_date']) : now();
            $expiryDate = !empty($data['expiry_date']) ? Carbon::parse($data['expiry_date']) : null;
            
            // Create local license record (include soft-deleted to avoid unique constraint violation)
            $license = License::withTrashed()->updateOrCreate(
                ['license_key' => $licenseKey],
                [
                    'customer_name' => $customerName,
                    'customer_email' => $data['customer_email'] ?? null,
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'business_name' => $data['business_name'] ?? null,
                    'status' => 'active',
                    'license_type' => $data['license_type'],
                    'start_date' => $startDate,
                    'expiry_date' => $expiryDate,
                    'max_users' => $data['max_users'] ?? null,
                    'max_tables' => $data['max_tables'] ?? null,
                    'activated_by_ip' => $ip,
                    'activated_at' => now(),
                    'last_checked_at' => now(),
                ]
            );

            // Restore if soft-deleted
            if ($license->trashed()) {
                $license->restore();
            }

            // Clear cache
            Cache::forget('current_license');

            return [
                'success' => true,
                'message' => 'License berhasil diaktifkan!',
                'license' => $license
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('License activation connection error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke License Manager. Pastikan aplikasi License Manager berjalan di ' . \license_config('server_url', 'http://localhost:8000')
            ];
        } catch (\Exception $e) {
            \Log::error('License activation error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal mengaktifkan license: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check license validity (with caching)
     */
    public function check(): array
    {
        $license = Cache::remember('current_license', 3600, function () {
            return License::current();
        });

        if (!$license) {
            return [
                'valid' => false,
                'message' => 'Aplikasi belum diaktivasi.'
            ];
        }

        // Update last checked
        $license->update(['last_checked_at' => now()]);

        if (!$license->isValid()) {
            Cache::forget('current_license');
            return [
                'valid' => false,
                'message' => 'License tidak valid atau sudah kadaluarsa.'
            ];
        }

        return [
            'valid' => true,
            'license' => $license,
            'expiring_soon' => $license->isExpiringSoon(),
            'days_remaining' => $license->daysRemaining()
        ];
    }

    /**
     * Update/renew license from License Manager
     * If the new key is found on server → activate with new data
     * If not found → keep current license unchanged
     */
    public function renewFromServer(string $licenseKey, string $ip): array
    {
        $connectionCheck = $this->checkConnection();
        if (!$connectionCheck['connected']) {
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke License Manager. Pastikan server aktif.'
            ];
        }

        $serverUrl = \license_config('server_url', 'http://localhost:8000');
        $lookupUrl = rtrim($serverUrl, '/') . '/api/license/lookup';

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->post($lookupUrl, [
                'license_key' => $licenseKey,
            ]);

            $body = trim($response->body(), "\xEF\xBB\xBF");
            $lookupData = json_decode($body, true);

            if (!$response->successful() || !($lookupData['found'] ?? false)) {
                return [
                    'success' => false,
                    'message' => 'License key tidak ditemukan di License Manager. License saat ini tetap digunakan.'
                ];
            }

            $data = $lookupData['data'];

            // Check license status - reject if active or expired
            $status = strtolower($data['status'] ?? '');
            if ($status === 'active') {
                return [
                    'success' => false,
                    'message' => 'License key ini sudah aktif/digunakan. Tidak dapat digunakan lagi.'
                ];
            }
            if ($status === 'expired') {
                return [
                    'success' => false,
                    'message' => 'License key ini sudah kadaluarsa. Tidak dapat digunakan.'
                ];
            }

            // Now activate the new license via the activate endpoint
            $activateUrl = rtrim($serverUrl, '/') . '/api/license/activate';
            $customerName = $data['customer_name'] ?? $data['business_name'] ?? '';

            $activateResponse = \Illuminate\Support\Facades\Http::timeout(10)->post($activateUrl, [
                'license_key' => $licenseKey,
                'customer_name' => $customerName,
                'ip_address' => $ip,
                'application' => 'POS-RESTO',
            ]);

            $activateBody = trim($activateResponse->body(), "\xEF\xBB\xBF");
            $activateData = json_decode($activateBody, true);

            if (!$activateResponse->successful() || !($activateData['success'] ?? false)) {
                return [
                    'success' => false,
                    'message' => $activateData['message'] ?? 'Gagal mengaktifkan license baru dari server.'
                ];
            }

            $serverData = $activateData['data'];

            // Use expiry_date from License Manager (server is the source of truth)
            $startDate = !empty($serverData['activation_date']) ? Carbon::parse($serverData['activation_date']) : now();
            $expiryDate = !empty($serverData['expiry_date']) ? Carbon::parse($serverData['expiry_date']) : null;

            // Soft-delete old license if key is different
            $currentLicense = License::current();
            if ($currentLicense && $currentLicense->license_key !== $licenseKey) {
                $currentLicense->update(['status' => 'expired']);
                $currentLicense->delete();
            }

            // Create/update local license record
            $license = License::withTrashed()->updateOrCreate(
                ['license_key' => $licenseKey],
                [
                    'customer_name' => $customerName,
                    'customer_email' => $serverData['customer_email'] ?? $data['customer_email'] ?? null,
                    'customer_phone' => $serverData['customer_phone'] ?? $data['customer_phone'] ?? null,
                    'business_name' => $serverData['business_name'] ?? $data['business_name'] ?? null,
                    'status' => 'active',
                    'license_type' => $serverData['license_type'],
                    'start_date' => $startDate,
                    'expiry_date' => $expiryDate,
                    'max_users' => $serverData['max_users'] ?? null,
                    'max_tables' => $serverData['max_tables'] ?? null,
                    'activated_by_ip' => $ip,
                    'activated_at' => now(),
                    'last_checked_at' => now(),
                ]
            );

            // Restore if soft-deleted
            if ($license->trashed()) {
                $license->restore();
            }

            Cache::forget('current_license');

            return [
                'success' => true,
                'message' => 'License berhasil diperbarui!',
                'license' => $license
            ];

        } catch (\Exception $e) {
            \Log::error('License renew error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui license. License saat ini tetap digunakan.'
            ];
        }
    }

    /**
     * Create a new license
     */
    public function create(array $data): License
    {
        $licenseKey = License::generateKey();
        
        // Set start date, default to now if not provided
        $startDate = $data['start_date'] ?? now();

        // Calculate expiry date based on license type
        $expiryDate = $this->calculateExpiryDate($data['license_type'], $startDate);

        return License::create([
            'license_key' => $licenseKey,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'business_name' => $data['business_name'] ?? null,
            'status' => 'trial',
            'license_type' => $data['license_type'],
            'start_date' => $startDate,
            'expiry_date' => $expiryDate,
            'max_users' => $data['max_users'] ?? null,
            'max_tables' => $data['max_tables'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Renew a license
     */
    public function renew(License $license, string $licenseType): bool
    {
        $startDate = $license->expiry_date && $license->expiry_date->isFuture() 
            ? $license->expiry_date 
            : now();

        $expiryDate = $this->calculateExpiryDate($licenseType, $startDate);

        $license->update([
            'license_type' => $licenseType,
            'expiry_date' => $expiryDate,
            'status' => 'active',
        ]);

        Cache::forget('current_license');

        return true;
    }

    /**
     * Calculate expiry date based on license type
     */
    protected function calculateExpiryDate(string $licenseType, $startDate = null): ?Carbon
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now();

        return match ($licenseType) {
            'trial' => $startDate->copy()->addDays(14),
            'monthly' => $startDate->copy()->addMonth(),
            'yearly' => $startDate->copy()->addYear(),
            'lifetime' => null,
            default => $startDate->copy()->addYear(),
        };
    }

    /**
     * Get license statistics for dashboard
     */
    public function getStats(): array
    {
        return [
            'total' => License::count(),
            'active' => License::where('status', 'active')->count(),
            'expired' => License::where('status', 'expired')->count(),
            'suspended' => License::where('status', 'suspended')->count(),
            'trial' => License::where('status', 'trial')->count(),
            'expiring_soon' => License::where('status', 'active')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>=', now())
                ->count(),
        ];
    }

    /**
     * Verify license with remote server
     * Returns true if license is valid on server, false if deleted/invalid
     */
    public function verifyWithServer(string $licenseKey): bool
    {
        $apiUrl = \license_config('server_url', 'http://localhost:8000') . '/api/license/verify';
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->post($apiUrl, [
                'license_key' => $licenseKey,
            ]);

            // Parse response with BOM handling
            $body = trim($response->body(), "\xEF\xBB\xBF");
            $responseData = json_decode($body, true);

            if ($responseData && isset($responseData['valid'])) {
                return $responseData['valid'] === true;
            }

            return false;

        } catch (\Exception $e) {
            \Log::error('License server verification failed: ' . $e->getMessage());
            // If server unreachable, assume license is still valid (don't force reactivation on network issues)
            return true;
        }
    }

    /**
     * Check if local license still exists on server
     */
    public function syncWithServer(): array
    {
        $license = License::current();
        
        if (!$license) {
            return [
                'synced' => true,
                'action' => 'none',
                'message' => 'No local license to sync'
            ];
        }

        $isValidOnServer = $this->verifyWithServer($license->license_key);

        if (!$isValidOnServer) {
            // License deleted on server, remove local copy
            \Log::warning('License deleted on server, removing local copy', [
                'license_key' => $license->license_key,
                'customer_name' => $license->customer_name
            ]);

            $license->delete();
            Cache::forget('current_license');

            return [
                'synced' => true,
                'action' => 'deleted',
                'message' => 'License telah dihapus dari server. Silakan aktivasi ulang.'
            ];
        }

        return [
            'synced' => true,
            'action' => 'verified',
            'message' => 'License valid'
        ];
    }
}

