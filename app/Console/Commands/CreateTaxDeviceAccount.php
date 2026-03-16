<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Command untuk membuat/mereset akun API khusus pihak ketiga (device perekaman pajak).
 * 
 * Akun ini role=tax_device, hanya bisa GET (read-only) melalui API,
 * dan hanya melihat order flag=0.
 * 
 * Usage: php artisan tax:create-device-account
 */
class CreateTaxDeviceAccount extends Command
{
    protected $signature = 'tax:create-device-account 
                            {--name=Tax Device : Nama device}
                            {--reset : Reset password jika sudah ada}';

    protected $description = 'Buat akun API khusus untuk device perekaman pajak (read-only, flag=0 only)';

    public function handle()
    {
        $name = $this->option('name');
        $email = 'tax_device@posresto.local';
        $password = Str::random(16);

        $existing = User::where('email', $email)->first();

        if ($existing && !$this->option('reset')) {
            $this->warn("Akun tax device sudah ada (ID: {$existing->id})");
            $this->info("Gunakan --reset untuk mereset password.");
            $this->info("Email: {$email}");
            return 0;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => 'tax_device',
                'password' => Hash::make($password),
                'role' => 'tax_device',
                'is_active' => true,
            ]
        );

        $this->newLine();
        $this->info('=== AKUN TAX DEVICE ===');
        $this->newLine();
        $this->table(
            ['Item', 'Value'],
            [
                ['User ID', $user->id],
                ['Email', $email],
                ['Password', $password],
                ['Role', 'tax_device'],
                ['Access', 'API Read-Only (GET) — flag=0 orders only'],
            ]
        );
        $this->newLine();
        $this->warn('SIMPAN PASSWORD INI! Tidak bisa dilihat lagi.');
        $this->info("Login endpoint: POST /api/login {email, password}");
        $this->info("Lalu gunakan JWT token di header: Authorization: Bearer {token}");
        $this->newLine();

        return 0;
    }
}
