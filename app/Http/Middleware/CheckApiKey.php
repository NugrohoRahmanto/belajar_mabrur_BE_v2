<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $clientKey = $request->header('X-API-KEY');
        $serverKey = env('API_KEY');

        // Cek apakah API Key tidak ada atau salah
        if (!$clientKey || $clientKey !== $serverKey) {
            return response()->json([
                'code'    => 403,
                'status'  => 'Forbidden',
                'message' => 'Invalid or missing API Key'
            ], 403);
        }

        return $next($request);
    }
}
