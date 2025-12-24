<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'API token tələb olunur'
            ], 401);
        }

        $validToken = config('services.api.token');

        if ($token !== $validToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Yanlış API token'
            ], 401);
        }

        return $next($request);
    }
}
