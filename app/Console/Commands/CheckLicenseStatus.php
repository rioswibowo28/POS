<?php

namespace App\Console\Commands;

use App\Services\LicenseManagerService;
use Illuminate\Console\Command;

class CheckLicenseStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'license:check
                          {--force : Force refresh license data from server}
                          {--clear-cache : Clear cached license data}';

    /**
     * The console command description.
     */
    protected $description = 'Check license status with License Manager server';

    protected LicenseManagerService $licenseService;

    public function __construct(LicenseManagerService $licenseService)
    {
        parent::__construct();
        $this->licenseService = $licenseService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking License Status...');
        $this->newLine();

        // Clear cache if requested
        if ($this->option('clear-cache')) {
            $this->licenseService->clearCache();
            $this->warn('Cache cleared.');
            $this->newLine();
        }

        // Get license info
        $forceRefresh = $this->option('force');
        $licenseInfo = $this->licenseService->getLicenseInfo();

        if (!$licenseInfo['valid']) {
            $this->error('❌ License is INVALID or EXPIRED');
            $this->error('Message: ' . ($licenseInfo['message'] ?? 'Unknown error'));
            $this->newLine();
            $this->warn('Please contact support or update your license key.');
            return self::FAILURE;
        }

        // Display license information
        $this->displayLicenseInfo($licenseInfo);

        return self::SUCCESS;
    }

    protected function displayLicenseInfo(array $info): void
    {
        $this->info('✅ License is VALID');
        $this->newLine();

        $headers = ['Property', 'Value'];
        $rows = [
            ['License Key', $info['license_key'] ?? 'N/A'],
            ['Status', strtoupper($info['status'] ?? 'Unknown')],
            ['Expiry Date', $info['expiry_date'] ?? 'Lifetime'],
            ['Days Remaining', $this->formatDaysRemaining($info['days_remaining'] ?? null)],
            ['Max Users', $info['max_users'] ?? 'Unlimited'],
            ['Max Tables', $info['max_tables'] ?? 'Unlimited'],
        ];

        $this->table($headers, $rows);

        // Warning if expiring soon
        $daysRemaining = $info['days_remaining'] ?? null;
        if ($daysRemaining !== null && $daysRemaining > 0 && $daysRemaining <= 30) {
            $this->newLine();
            $this->warn("⚠️  LICENSE EXPIRING SOON: {$daysRemaining} days remaining");
            $this->warn('Please renew your license to avoid service interruption.');
        }
    }

    protected function formatDaysRemaining(?int $days): string
    {
        if ($days === null) {
            return 'Lifetime';
        }

        if ($days < 0) {
            return 'EXPIRED (' . abs($days) . ' days ago)';
        }

        if ($days === 0) {
            return 'Expires TODAY';
        }

        return "{$days} days";
    }
}
