<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogAdminAction
{
    /**
     * Log setiap aksi admin ke Laravel log.
     * Mencatat user_id, email, HTTP method, URL path, IP, dan timestamp.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log setelah request diproses, hanya jika user terautentikasi
        if ($request->user()) {
            Log::info('Admin action', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'method' => $request->method(),
                'url' => $request->path(),
                'ip' => $request->ip(),
                'timestamp' => now('Asia/Makassar')->toDateTimeString(),
            ]);
        }

        return $response;
    }
}
