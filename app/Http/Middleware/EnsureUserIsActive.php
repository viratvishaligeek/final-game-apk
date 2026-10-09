<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && isset($user->status) && $user->status !== 'active') {
            $token = $user->currentAccessToken();

            // Sanctum's first-party/session guard can return a transient token
            // without delete(), so revoke only persisted personal-access tokens.
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }

            return response()->json([
                'success' => false,
                'message' => 'Your account is Inactive Or Blocked. Please contact support.',
                'errors'  => [
                    'account' => ['Your account is currently Inactive Or Blocked.']
                ]
            ], 403);
        }
        return $next($request);
    }
}
