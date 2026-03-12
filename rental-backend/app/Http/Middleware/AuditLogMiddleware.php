<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log state-changing requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = $request->user();
            
            // Skip logging if no user is authenticated (unless it's a login attempt, handled separately)
            if (!$user) {
                return $response;
            }

            $excludeFields = ['password', 'password_confirmation', '_token'];
            $newValues = $request->except($excludeFields);

            \App\Services\AuditService::log(
                event: strtolower($request->method()) . '_request',
                module: $this->getModuleFromUrl($request->path()),
                newValues: $newValues
            );
        }

        return $response;
    }

    private function getModuleFromUrl($path)
    {
        $segments = explode('/', $path);
        return $segments[0] ?? 'general';
    }
}
