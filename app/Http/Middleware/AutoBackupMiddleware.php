<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use Carbon\Carbon;

class AutoBackupMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Execute after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            $this->checkAndRunBackup();
        } catch (\Exception $e) {
            Log::error("AutoBackupMiddleware Failed: " . $e->getMessage());
        }
    }

    private function checkAndRunBackup(): void
    {
        // Cache setting selama 5 menit agar tidak terus-terusan memanggil database on every request
        $settings = Cache::remember("backup_settings_cache", 300, function() {
            return [
                "s1_enabled" => Setting::get("backup_schedule_1_enabled", "1"),
                "s1_time"    => Setting::get("backup_schedule_1", "12:00"),
                "s2_enabled" => Setting::get("backup_schedule_2_enabled", "1"),
                "s2_time"    => Setting::get("backup_schedule_2", "23:55"),
                "keep_days"  => Setting::get("backup_keep_days", "30"),
                "path"       => Setting::get("backup_path", ""),
            ];
        });

        // Time checks
        $now = Carbon::now();
        $dateStr = $now->format("Y-m-d");
        $currentTimeStr = $now->format("H:i");

        // Build args arguments for artisan
        $args = ["--keep" => $settings["keep_days"]];
        if (!empty($settings["path"])) {
            $args["--path"] = $settings["path"];
        }

        // Schedule 1
        if ($settings["s1_enabled"] == "1" && !empty($settings["s1_time"])) {
            $s1Time = date("H:i", strtotime($settings["s1_time"]));
            if ($currentTimeStr >= $s1Time) {
                $cacheKey = "backup_done_s1_" . $dateStr;
                if (!Cache::has($cacheKey)) {
                    Cache::put($cacheKey, true, now()->addHours(24)); // Set cache agar hari ini tidak jalan lagi
                    try {
                        Artisan::call("db:backup", $args);
                    } catch(\Exception $e) {
                        Log::error("Auto Backup S1 failed: " . $e->getMessage());
                        Cache::forget($cacheKey);
                    }
                }
            }
        }

        // Schedule 2
        if ($settings["s2_enabled"] == "1" && !empty($settings["s2_time"])) {
            $s2Time = date("H:i", strtotime($settings["s2_time"]));
            if ($currentTimeStr >= $s2Time) {
                $cacheKey = "backup_done_s2_" . $dateStr;
                if (!Cache::has($cacheKey)) {
                    Cache::put($cacheKey, true, now()->addHours(24));
                    try {
                        Artisan::call("db:backup", $args);
                    } catch(\Exception $e) {
                        Log::error("Auto Backup S2 failed: " . $e->getMessage());
                        Cache::forget($cacheKey);
                    }
                }
            }
        }
    }
}
