<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\SettingRepository;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        private SettingRepository $settingRepository
    ) {}

    public function index()
    {
        $settings = $this->settingRepository->all()->pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'restaurant_name' => 'required|string|max:255',
                'restaurant_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:5120',
                'restaurant_address' => 'required|string',
                'restaurant_phone' => 'required|string|max:50',
                'restaurant_email' => 'required|email|max:255',
                'tax_percentage' => 'required|numeric|min:0|max:100',
                'tax_type' => 'required|in:exclude,include',
                'service_charge' => 'required|numeric|min:0|max:100',
                'receipt_footer' => 'nullable|string',
                'currency' => 'required|string|max:10',
                'customer_display_poster' => 'nullable|image|mimes:png,jpg,jpeg|max:10240',
                'customer_display_mode' => 'nullable|in:local,network',
                'midtrans_enabled' => 'nullable|boolean',
                'midtrans_merchant_id' => 'nullable|string|max:255',
                'midtrans_client_key' => 'nullable|string|max:255',
                'midtrans_server_key' => 'nullable|string|max:255',
                'midtrans_is_production' => 'nullable|boolean',
                // License API Settings
                'license_server_url' => 'required|url|max:255',
                'license_key' => 'required|string|max:255',
                'license_check_interval' => 'required|integer|min:3600',
                'license_grace_period' => 'required|integer|min:0|max:30',
                'license_auto_check' => 'nullable|boolean',
                // Order Limit Settings
                'order_limit_enabled' => 'nullable|boolean',
                'order_limit_amount' => 'nullable|integer|min:1',
                'order_limit_start' => 'nullable|date_format:H:i',
                'order_limit_end' => 'nullable|date_format:H:i',
                // Backup Settings
                'backup_path' => 'nullable|string|max:500',
                'backup_schedule_1_enabled' => 'nullable|boolean',
                'backup_schedule_1' => 'nullable|date_format:H:i',
                'backup_schedule_2_enabled' => 'nullable|boolean',
                'backup_schedule_2' => 'nullable|date_format:H:i',
                'backup_keep_days' => 'nullable|integer|min:1|max:365',
            ]);
            
            // Additional validation: If Midtrans is enabled, require credentials
            if ($request->has('midtrans_enabled') && $request->midtrans_enabled) {
                $midtransRules = [
                    'midtrans_client_key' => 'required|string',
                    'midtrans_server_key' => 'required|string',
                ];
                
                $request->validate($midtransRules, [
                    'midtrans_client_key.required' => 'Client Key is required when Midtrans is enabled',
                    'midtrans_server_key.required' => 'Server Key is required when Midtrans is enabled',
                ]);
            }

            // Handle logo upload
            if ($request->hasFile('restaurant_logo')) {
                try {
                    // Delete old logo if exists
                    $oldLogo = $this->settingRepository->getByKey('restaurant_logo');
                    if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                        \Storage::disk('public')->delete($oldLogo);
                    }
                    
                    // Upload new logo
                    $logoPath = $request->file('restaurant_logo')->store('logos', 'public');
                    $this->settingRepository->setByKey('restaurant_logo', $logoPath, 'string');
                    unset($validated['restaurant_logo']);
                } catch (\Exception $e) {
                    \Log::error('Failed to upload logo', ['error' => $e->getMessage()]);
                    // Continue anyway, don't fail the entire update
                }
            }

            // Handle customer display poster upload
            if ($request->hasFile('customer_display_poster')) {
                try {
                    // Delete old poster if exists
                    $oldPoster = $this->settingRepository->getByKey('customer_display_poster');
                    if ($oldPoster && \Storage::disk('public')->exists($oldPoster)) {
                        \Storage::disk('public')->delete($oldPoster);
                    }
                    
                    // Upload new poster
                    $posterPath = $request->file('customer_display_poster')->store('posters', 'public');
                    $this->settingRepository->setByKey('customer_display_poster', $posterPath, 'string');
                    unset($validated['customer_display_poster']);
                } catch (\Exception $e) {
                    \Log::error('Failed to upload customer display poster', ['error' => $e->getMessage()]);
                    // Continue anyway, don't fail the entire update
                }
            }

            foreach ($validated as $key => $value) {
                // Determine type
                if (in_array($key, ['tax_percentage', 'service_charge', 'license_check_interval', 'license_grace_period', 'backup_keep_days', 'order_limit_amount'])) {
                    $type = 'number';
                } elseif (in_array($key, ['midtrans_is_production', 'license_auto_check', 'backup_schedule_1_enabled', 'backup_schedule_2_enabled', 'order_limit_enabled'])) {
                    $type = 'boolean';
                    $value = $value ? '1' : '0';
                } else {
                    $type = 'string';
                }
                
                $this->settingRepository->setByKey($key, $value, $type);
            }
            
            // Update .env file for license settings (optional but recommended)
            try {
                $envUpdated = $this->updateEnvFile([
                    'LICENSE_SERVER_URL' => $validated['license_server_url'],
                    'LICENSE_KEY' => $validated['license_key'],
                    'LICENSE_CHECK_INTERVAL' => $validated['license_check_interval'],
                    'LICENSE_GRACE_PERIOD' => $validated['license_grace_period'],
                    'LICENSE_AUTO_CHECK' => $request->has('license_auto_check') ? 'true' : 'false',
                ]);
                
                if (!$envUpdated) {
                    \Log::warning('Failed to update .env file, but settings saved to database');
                }
            } catch (\Exception $e) {
                \Log::error('Error updating .env file', ['error' => $e->getMessage()]);
                // Continue anyway, settings are saved to database
            }

            return redirect()->route('settings.index')->with('success', 'Settings updated successfully');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions to show validation errors
            throw $e;
            
        } catch (\Exception $e) {
            \Log::error('Error updating settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('settings.index')
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Test license API connection
     */
    public function testLicenseConnection(Request $request)
    {
        $request->validate([
            'server_url' => 'required|url',
            'license_key' => 'required|string',
        ]);
        
        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'verify' => false, // Untuk development, set true di production
                'http_errors' => false, // Don't throw exception on 4xx/5xx
            ]);
            
            $serverUrl = rtrim($request->server_url, '/'); // Remove trailing slash
            $endpoint = $serverUrl . '/api/license/verify';
            
            \Log::info('Testing license connection', [
                'endpoint' => $endpoint,
                'license_key' => substr($request->license_key, 0, 10) . '...',
            ]);
            
            $response = $client->post($endpoint, [
                'json' => [
                    'license_key' => $request->license_key,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            
            \Log::info('License API response', [
                'status_code' => $statusCode,
                'body' => substr($body, 0, 200),
            ]);
            
            // Handle HTTP errors (4xx, 5xx)
            if ($statusCode >= 400) {
                $data = json_decode($body, true);
                
                if ($statusCode === 404) {
                    return response()->json([
                        'success' => false,
                        'message' => 'License not found. Please check your license key or create a new license in License Manager.',
                        'debug' => [
                            'endpoint' => $endpoint,
                            'status' => $statusCode,
                            'response' => $data['message'] ?? 'No message',
                        ]
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $data['message'] ?? 'License Manager returned error: ' . $statusCode,
                    'debug' => [
                        'endpoint' => $endpoint,
                        'status' => $statusCode,
                    ]
                ]);
            }
            
            $data = json_decode($body, true);
            
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid response from License Manager (not valid JSON)',
                    'debug' => [
                        'endpoint' => $endpoint,
                        'raw_response' => substr($body, 0, 100),
                    ]
                ]);
            }
            
            // Check if license is valid
            if (isset($data['valid']) && $data['valid'] === true) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful! License is valid.',
                    'license_type' => $data['license']['license_type'] ?? 'N/A',
                    'status' => $data['license']['status'] ?? 'N/A',
                    'customer_name' => $data['license']['customer_name'] ?? '',
                ]);
            }
            
            // License exists but not valid
            if (isset($data['success']) && $data['success'] === false) {
                return response()->json([
                    'success' => false,
                    'message' => $data['message'] ?? 'License verification failed',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Unexpected response format from License Manager',
                'debug' => [
                    'has_valid_key' => isset($data['valid']),
                    'has_success_key' => isset($data['success']),
                ]
            ]);
            
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot connect to License Manager server at ' . $request->server_url,
                'debug' => [
                    'error' => 'Connection timeout or refused',
                    'suggestion' => 'Make sure License Manager is running and URL is correct',
                ]
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('License test connection error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'debug' => [
                    'type' => get_class($e),
                ]
            ], 200);
        }
    }
    
    /**
     * Update .env file with new values
     */
    private function updateEnvFile(array $data)
    {
        try {
            $envPath = base_path('.env');
            
            if (!file_exists($envPath)) {
                \Log::warning('updateEnvFile: .env file not found');
                return false;
            }
            
            // Check if file is writable
            if (!is_writable($envPath)) {
                \Log::error('updateEnvFile: .env file is not writable');
                return false;
            }
            
            $envContent = file_get_contents($envPath);
            
            if ($envContent === false) {
                \Log::error('updateEnvFile: Failed to read .env file');
                return false;
            }
            
            foreach ($data as $key => $value) {
                // Escape special characters in value
                $value = str_replace('"', '\"', (string)$value);
                $value = str_replace('$', '\$', $value); // Escape $ sign
                
                // Check if key exists
                if (preg_match("/^{$key}=/m", $envContent)) {
                    // Update existing key
                    $envContent = preg_replace(
                        "/^{$key}=.*/m",
                        "{$key}=\"{$value}\"",
                        $envContent
                    );
                } else {
                    // Add new key at the end
                    $envContent .= "\n{$key}=\"{$value}\"";
                }
            }
            
            $result = file_put_contents($envPath, $envContent);
            
            if ($result === false) {
                \Log::error('updateEnvFile: Failed to write to .env file');
                return false;
            }
            
            \Log::info('updateEnvFile: Successfully updated .env file', ['keys' => array_keys($data)]);
            
            // Don't call config:clear here as it might cause issues
            // User should manually clear cache if needed
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('updateEnvFile: Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Remove restaurant logo
     */
    public function removeLogo()
    {
        try {
            $oldLogo = $this->settingRepository->getByKey('restaurant_logo');
            
            if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                \Storage::disk('public')->delete($oldLogo);
            }
            
            // Remove from database
            \App\Models\Setting::where('key', 'restaurant_logo')->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Logo removed successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error removing logo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove logo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removePoster()
    {
        try {
            $oldPoster = $this->settingRepository->getByKey('customer_display_poster');
            
            if ($oldPoster && \Storage::disk('public')->exists($oldPoster)) {
                \Storage::disk('public')->delete($oldPoster);
            }
            
            // Remove from database
            \App\Models\Setting::where('key', 'customer_display_poster')->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Poster removed successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error removing poster', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove poster: ' . $e->getMessage()
            ], 500);
        }
    }
}
