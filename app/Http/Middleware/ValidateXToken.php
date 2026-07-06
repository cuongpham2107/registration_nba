<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateXToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('x-token');

        if (!$token || $token !== config('services.api_x_token')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
