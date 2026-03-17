<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function index()
    {
        $settingPath = Setting::get('backup_path', '');
        $backupPath = $settingPath ?: storage_path('app/backups');
        $backups = [];

        if (File::isDirectory($backupPath)) {
            $files = File::glob($backupPath . '/backup_*.sql');
            rsort($files);

            foreach ($files as $file) {
                $size = filesize($file);
                $backups[] = [
                    'name'      => basename($file),
                    'size'      => $size >= 1048576
                        ? round($size / 1048576, 2) . ' MB'
                        : round($size / 1024, 2) . ' KB',
                    'size_raw'  => $size,
                    'date'      => date('d/m/Y H:i:s', filemtime($file)),
                    'timestamp' => filemtime($file),
                ];
            }
        }

        $totalSize = collect($backups)->sum('size_raw');
        $totalSizeDisplay = $totalSize >= 1048576
            ? round($totalSize / 1048576, 2) . ' MB'
            : round($totalSize / 1024, 2) . ' KB';

        $backupPath = Setting::get('backup_path', '') ?: storage_path('app/backups');
        $schedule1 = Setting::get('backup_schedule_1', '12:00');
        $schedule1Enabled = Setting::get('backup_schedule_1_enabled', '1') == '1';
        $schedule2 = Setting::get('backup_schedule_2', '23:55');
        $schedule2Enabled = Setting::get('backup_schedule_2_enabled', '1') == '1';
        $keepDays = Setting::get('backup_keep_days', 30);

        return view('backups.index', compact('backups', 'totalSizeDisplay', 'backupPath', 'schedule1', 'schedule1Enabled', 'schedule2', 'schedule2Enabled', 'keepDays'));
    }

    public function create()
    {
        try {
            Artisan::call('db:backup');
            $output = trim(Artisan::output());
            return redirect()->route('backups.index')
                ->with('success', 'Backup berhasil dibuat! ' . $output);
        } catch (\Exception $e) {
            return redirect()->route('backups.index')
                ->with('error', 'Backup gagal: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $filename = basename($filename);
        $settingPath = \App\Models\Setting::get('backup_path');
        $backupPath = $settingPath ?: storage_path('app/backups');
        $filePath = rtrim($backupPath, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($filePath)) {
            return redirect()->route('backups.index')
                ->with('error', 'File backup tidak ditemukan.');
        }

        return response()->download($filePath);
    }

    public function destroy($filename)
    {
        $filename = basename($filename);
        $settingPath = \App\Models\Setting::get('backup_path');
        $backupPath = $settingPath ?: storage_path('app/backups');
        $filePath = rtrim($backupPath, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (File::exists($filePath)) {
            File::delete($filePath);
            return redirect()->route('backups.index')
                ->with('success', 'Backup "' . $filename . '" berhasil dihapus.');
        }

        return redirect()->route('backups.index')
            ->with('error', 'File backup tidak ditemukan.');
    }
}
