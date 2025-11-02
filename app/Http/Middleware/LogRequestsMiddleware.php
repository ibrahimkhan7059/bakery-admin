<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Log incoming request
        if (str_contains($request->path(), 'ai') || str_contains($request->path(), 'debug')) {
            Log::info('🌐 Incoming Request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'content_type' => $request->header('Content-Type'),
                'auth_header' => $request->header('Authorization') ? 'present' : 'missing'
            ]);
        }

        $response = $next($request);

        // Log response for AI-related routes
        if (str_contains($request->path(), 'ai') || str_contains($request->path(), 'debug')) {
            Log::info('📤 Response', [
                'status' => $response->getStatusCode(),
                'path' => $request->path(),
                'response_preview' => substr($response->getContent(), 0, 200)
            ]);
        }

        return $response;
    }
}
