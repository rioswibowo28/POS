<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk mencatat semua akses API dari pihak ketiga.
 * Log disimpan di storage/logs/api-audit.log
 */
class ApiAuditLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log setelah response dibuat
        $user = auth('api')->user();

        $logData = [
            'timestamp'  => now()->toIso8601String(),
            'ip'         => $request->ip(),
            'method'     => $request->method(),
            'url'        => $request->fullUrl(),
            'user_id'    => $user?->id,
            'user_role'  => $user?->role,
            'user_agent' => $request->userAgent(),
            'status'     => $response->getStatusCode(),
        ];

        Log::channel('api_audit')->info('API Access', $logData);

        return $response;
    }
}
