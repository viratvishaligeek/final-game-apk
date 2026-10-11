<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommunityUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->guest(route('community.login'));
        }

        if ($user->status !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('community.login')->withErrors([
                'phone' => 'Your account is inactive. Please contact support.',
            ]);
        }

        return $next($request);
    }
}
