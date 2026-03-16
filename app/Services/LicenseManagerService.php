<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class LicenseManagerService
{
    protected string $serverUrl;
    protected string $licenseKey;
    protected int $cacheTtl;
    protected string $cacheKey;

    public function __construct()
    {
        $this->serverUrl = rtrim(config('license.server_url'), '/');
        $this->licenseKey = config('license.license_key');
        $this->cacheTtl = config('license.cache_ttl', 3600);
        $this->cacheKey = config('license.cache_key', 'pos_resto_license_data');
    }

    /**
     * Verify license with the License Manager server.
     */
    public function verifyLicense(bool $forceRefresh = false): ?array
    {
        if (!$this->licenseKey) {
            Log::error('License verification failed: No license key configured');
            return null;
        }

        // Try cache first unless forced refresh
        if (!$forceRefresh) {
            $cached = Cache::get($this->cacheKey);
            if ($cached && isset($cached['valid']) && $cached['valid']) {
                return $cached;
            }
        }

        try {
            // Call License Manager API
            $response = Http::timeout(10)
                ->post("{$this->serverUrl}/api/license/verify", [
                    'license_key' => $this->licenseKey,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['valid'] ?? false) {
                    // Cache the result
                    Cache::put($this->cacheKey, $data, $this->cacheTtl);
                    
                    Log::info('License verified successfully', [
                        'license_key' => substr($this->licenseKey, 0, 8) . '...',
                        'expiry_date' => $data['license']['expiry_date'] ?? null,
                    ]);
                    
                    return $data;
                }
            }

            Log::warning('License verification failed', [
                'license_key' => substr($this->licenseKey, 0, 8) . '...',
                'status' => $response->status(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('License verification error: ' . $e->getMessage());
            
            // In offline mode, try to use cached license
            if (config('license.offline_mode')) {
                return $this->verifyOfflineToken();
            }
            
            return null;
        }
    }

    /**
     * Verify offline signed token.
     */
    public function verifyOfflineToken(): ?array
    {
        $token = config('license.offline_token');
        
        if (!$token) {
            return null;
        }

        try {
            $response = Http::timeout(5)
                ->post("{$this->serverUrl}/api/license/verify-token", [
                    'token' => $token,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['valid'] ?? false) {
                    Cache::put($this->cacheKey, $data, $this->cacheTtl * 24); // Cache for 24 hours
                    return $data;
                }
            }
        } catch (Exception $e) {
            Log::error('Offline token verification error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get cached license data.
     */
    public function getCachedLicense(): ?array
    {
        return Cache::get($this->cacheKey);
    }

    /**
     * Check if license is valid.
     */
    public function isLicenseValid(): bool
    {
        $license = $this->verifyLicense();
        return $license && ($license['valid'] ?? false);
    }

    /**
     * Get license information.
     */
    public function getLicenseInfo(): array
    {
        $license = $this->verifyLicense();
        
        if (!$license || !($license['valid'] ?? false)) {
            return [
                'valid' => false,
                'message' => 'Invalid or expired license',
            ];
        }

        $licenseData = $license['license'] ?? [];
        
        return [
            'valid' => true,
            'license_key' => $licenseData['license_key'] ?? 'Unknown',
            'status' => $licenseData['status'] ?? 'Unknown',
            'expiry_date' => $licenseData['expiry_date'] ?? null,
            'max_users' => $licenseData['max_users'] ?? null,
            'max_tables' => $licenseData['max_tables'] ?? null,
            'days_remaining' => $this->calculateDaysRemaining($licenseData['expiry_date'] ?? null),
        ];
    }

    /**
     * Calculate days remaining until expiry.
     * Counts from today (inclusive) to expiry date (inclusive).
     */
    protected function calculateDaysRemaining(?string $expiryDate): ?int
    {
        if (!$expiryDate) {
            return null; // Lifetime license
        }

        try {
            $now = new \DateTime();
            $now->setTime(0, 0, 0);
            
            $expiry = new \DateTime($expiryDate);
            $expiry->setTime(0, 0, 0);
            
            $diff = $now->diff($expiry);
            $diffDays = (int) $diff->days;
            
            // If expiry is in the past, return negative
            if ($expiry < $now) {
                return -$diffDays;
            }
            
            return $diffDays;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Generate hardware ID for license binding.
     */
    public function generateHardwareId(): string
    {
        $components = config('license.hardware_id_components', []);
        
        $data = implode('|', array_filter([
            $components['mac'] ?? gethostname(),
            $components['cpu'] ?? php_uname('m'),
            $components['disk'] ?? '',
        ]));

        return hash('sha256', $data);
    }

    /**
     * Request offline activation code.
     */
    public function requestOfflineActivation(): ?array
    {
        if (!$this->licenseKey) {
            return null;
        }

        try {
            $hardwareId = $this->generateHardwareId();
            
            $response = Http::timeout(10)
                ->post("{$this->serverUrl}/licenses/offline-activation", [
                    'license_key' => $this->licenseKey,
                    'hardware_id' => $hardwareId,
                ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (Exception $e) {
            Log::error('Offline activation request error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Clear cached license data.
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
