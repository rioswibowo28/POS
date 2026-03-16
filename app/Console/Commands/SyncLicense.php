<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LicenseService;

class SyncLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync local license with LICENSE-MANAGER server';

    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        parent::__construct();
        $this->licenseService = $licenseService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing license with server...');

        try {
            $result = $this->licenseService->syncWithServer();

            if ($result['action'] === 'deleted') {
                $this->error($result['message']);
                return 1;
            }

            $this->info($result['message']);
            return 0;

        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            return 1;
        }
    }
}
