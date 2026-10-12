<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SelectAdminSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $sites = config('sites.sites', []);
        if (auth('admin')->check()) {
            $requestedSite = (string) $request->query('site', '');
            if ($requestedSite !== '' && array_key_exists($requestedSite, $sites)) {
                $request->session()->put('admin_selected_site', $requestedSite);
            }

            $selectedSite = (string) $request->session()->get('admin_selected_site', '');
            if ($selectedSite !== '' && isset($sites[$selectedSite])) {
                config([
                    'sites.current_key' => $selectedSite,
                    'sites.current' => array_merge($sites[$selectedSite], ['key' => $selectedSite]),
                ]);
            }
        }

        return $next($request);
    }
}
