<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memastikan user tax_device hanya bisa READ,
 * tidak bisa membuat/mengubah/menghapus data.
 * 
 * Pihak ketiga (device perekaman pajak) hanya boleh GET requests.
 */
class TaxDeviceReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        if ($user && $user->role === 'tax_device') {
            // Tax device hanya boleh GET (read-only)
            if (!$request->isMethod('GET')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tax device access is read-only.',
                ], 403);
            }
        }

        return $next($request);
    }
}
