<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XAuthorizationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $xAuthToken = $request->header('X-Authorization');

        if (!$xAuthToken) {
            return response()->json([
                'success' => false,
                'message' => 'X-Authorization header is required',
            ], 401);
        }

        $user = User::where('api_token', $xAuthToken)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid X-Authorization token',
            ], 401);
        }

        if ($request->user() && $request->user()->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'X-Authorization token does not match authenticated user',
            ], 403);
        }

        return $next($request);
    }
}
