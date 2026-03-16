<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LicenseService;

class GenerateLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:generate 
                            {type : License type (trial, monthly, yearly, lifetime)}
                            {--name= : Customer name}
                            {--email= : Customer email}
                            {--phone= : Customer phone}
                            {--business= : Business name}
                            {--users= : Max users}
                            {--tables= : Max tables}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new license key';

    /**
     * Execute the console command.
     */
    public function handle(LicenseService $licenseService)
    {
        $type = $this->argument('type');
        
        if (!in_array($type, ['trial', 'monthly', 'yearly', 'lifetime'])) {
            $this->error('Invalid license type. Use: trial, monthly, yearly, or lifetime');
            return 1;
        }

        $data = [
            'license_type' => $type,
            'customer_name' => $this->option('name'),
            'customer_email' => $this->option('email'),
            'customer_phone' => $this->option('phone'),
            'business_name' => $this->option('business'),
            'max_users' => $this->option('users'),
            'max_tables' => $this->option('tables'),
        ];

        $this->info('Generating license...');
        
        $license = $licenseService->create($data);
        
        $this->newLine();
        $this->info('✓ License generated successfully!');
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('  LICENSE KEY: <fg=green;options=bold>' . $license->license_key . '</>');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->line('  Type:     ' . strtoupper($type));
        if ($license->expiry_date) {
            $this->line('  Expires:  ' . $license->expiry_date->format('d F Y'));
        } else {
            $this->line('  Expires:  <fg=green>Lifetime</>');
        }
        if ($data['customer_name']) {
            $this->line('  Customer: ' . $data['customer_name']);
        }
        if ($data['business_name']) {
            $this->line('  Business: ' . $data['business_name']);
        }
        $this->newLine();

        return 0;
    }
}
