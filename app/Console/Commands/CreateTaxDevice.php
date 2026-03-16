<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Command untuk membuat user khusus device perekaman pajak (pihak ketiga).
 * 
 * User ini hanya bisa login via API dan hanya bisa READ data (GET requests).
 * Semua data yang terlihat sudah difilter: hanya order flag=0.
 * 
 * Usage: php artisan tax:create-device
 */
class CreateTaxDevice extends Command
{
    protected $signature = 'tax:create-device 
                            {--name= : Nama device (default: Tax Recording Device)}
                            {--email= : Email login device}
                            {--password= : Password login device (auto-generate jika kosong)}';

    protected $description = 'Buat user untuk device perekaman pajak (pihak ketiga) - read-only API access';

    public function handle(): int
    {
        $name = $this->option('name') ?: 'Tax Recording Device';
        $email = $this->option('email') ?: $this->ask('Email untuk device pajak?', 'tax-device@posresto.local');
        $password = $this->option('password') ?: Str::random(16);

        // Check if user already exists
        $existing = User::where('email', $email)->first();
        if ($existing) {
            if (!$this->confirm("User dengan email {$email} sudah ada. Update ke role tax_device?")) {
                $this->info('Dibatalkan.');
                return 0;
            }
            $existing->update([
                'role' => 'tax_device',
                'name' => $name,
                'is_active' => true,
            ]);
            $this->info("User {$email} diupdate ke role tax_device.");
            return 0;
        }

        $user = User::create([
            'name' => $name,
            'username' => 'tax_device_' . Str::random(4),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'tax_device',
            'is_active' => true,
        ]);

        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║  TAX DEVICE USER BERHASIL DIBUAT                ║');
        $this->info('╠══════════════════════════════════════════════════╣');
        $this->info("║  Name     : {$name}");
        $this->info("║  Email    : {$email}");
        $this->info("║  Password : {$password}");
        $this->info("║  Role     : tax_device (read-only API)");
        $this->info('╠══════════════════════════════════════════════════╣');
        $this->info('║  Login via API:                                  ║');
        $this->info('║  POST /api/login                                ║');
        $this->info("║  {\"email\": \"{$email}\", \"password\": \"{$password}\"}");
        $this->info('╠══════════════════════════════════════════════════╣');
        $this->info('║  PENTING: Simpan password ini!                  ║');
        $this->info('║  Password tidak bisa dilihat lagi setelah ini.  ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->newLine();

        $this->warn('Akses user ini TERBATAS:');
        $this->line('  - Hanya bisa GET request (read-only)');
        $this->line('  - Hanya melihat order flag=0 (kena pajak)');
        $this->line('  - Tidak bisa membuat/ubah/hapus data');
        $this->line('  - Semua akses dicatat di storage/logs/api-audit.log');

        return 0;
    }
}
