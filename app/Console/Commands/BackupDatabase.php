<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--path= : Custom backup path} {--keep=30 : Keep backups for N days}';
    protected $description = 'Backup database MySQL secara otomatis';

    public function handle()
    {
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Ambil dari setting, lalu option, lalu default
        $settingPath = Setting::get('backup_path', '');
        $backupPath = $this->option('path') ?: ($settingPath ?: storage_path('app/backups'));
        $keepDays = (int) ($this->option('keep') ?: Setting::get('backup_keep_days', 30));

        // Buat folder
        if (!File::isDirectory($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "backup_{$dbName}_{$timestamp}.sql";
        $filePath = $backupPath . DIRECTORY_SEPARATOR . $fileName;

        // Find mysqldump
        $mysqldump = $this->findMysqldump();
        if (!$mysqldump) {
            $this->error('mysqldump tidak ditemukan!');
            Log::error('Backup gagal: mysqldump tidak ditemukan');
            return Command::FAILURE;
        }

        $this->info("Menggunakan: {$mysqldump}");

        // Build command
        $passArg = $dbPass ? '--password=' . $dbPass : '';
        $command = sprintf(
            '"%s" --host=%s --port=%s --user=%s %s --single-transaction --routines --triggers %s > "%s" 2>&1',
            $mysqldump, $dbHost, $dbPort, $dbUser, $passArg, $dbName, $filePath
        );

        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($filePath) && filesize($filePath) > 100) {
            $sizeMB = round(filesize($filePath) / 1024 / 1024, 2);
            $sizeKB = round(filesize($filePath) / 1024, 2);
            $displaySize = $sizeMB >= 1 ? "{$sizeMB} MB" : "{$sizeKB} KB";

            $this->info("Backup berhasil: {$fileName} ({$displaySize})");
            Log::info("Database backup berhasil: {$fileName} ({$displaySize})");

            // Cleanup old backups
            $deleted = $this->cleanOldBackups($backupPath, $keepDays);
            if ($deleted > 0) {
                $this->info("{$deleted} backup lama dihapus (>{$keepDays} hari)");
            }

            return Command::SUCCESS;
        }

        // Hapus file kosong jika gagal
        if (file_exists($filePath)) {
            File::delete($filePath);
        }

        $this->error('Backup gagal!');
        if (!empty($output)) {
            $this->error(implode("\n", $output));
        }
        Log::error('Database backup gagal: ' . implode("\n", $output));
        return Command::FAILURE;
    }

    protected function findMysqldump(): ?string
    {
        // Cari di Laragon (C:\ dan D:\)
        $searchPaths = ['C:\\laragon\\bin\\mysql', 'D:\\laragon\\bin\\mysql'];

        foreach ($searchPaths as $basePath) {
            if (!is_dir($basePath)) continue;

            $dirs = glob($basePath . '\\mysql-*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                $bin = $dir . '\\bin\\mysqldump.exe';
                if (file_exists($bin)) return $bin;
            }
        }

        // Cari di PATH
        exec('where mysqldump 2>nul', $pathOutput, $ret);
        if ($ret === 0 && !empty($pathOutput[0])) {
            return trim($pathOutput[0]);
        }

        // Fallback Linux
        exec('which mysqldump 2>/dev/null', $pathOutput2, $ret2);
        if ($ret2 === 0 && !empty($pathOutput2[0])) {
            return trim($pathOutput2[0]);
        }

        return null;
    }

    protected function cleanOldBackups(string $path, int $keepDays): int
    {
        $files = File::glob($path . '/backup_*.sql');
        $cutoff = now()->subDays($keepDays)->timestamp;
        $deleted = 0;

        foreach ($files as $file) {
            if (File::lastModified($file) < $cutoff) {
                File::delete($file);
                $deleted++;
            }
        }

        return $deleted;
    }
}
