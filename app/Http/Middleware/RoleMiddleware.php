<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        $roleArray = explode('|', $roles); // Convert "ADMIN,OWNER" into ['ADMIN', 'OWNER']

        if (!$user || !$user->relationLoaded('role')) {
            $user->load('role');
        }

        if (!$user->role || !in_array($user->role->name, $roleArray)) {
            return response()->json([
                'statusCode' => 403,
                'message' => 'Forbidden. You do not have permission to access this resource.',
            ], 403);
        }

        return $next($request);
    }
}
