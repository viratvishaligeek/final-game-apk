<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $sites = config('sites.sites', []);
        $defaultKey = config('sites.default');
        $resolvedKey = $defaultKey;
        $resolvedSite = $sites[$defaultKey] ?? config('sites.current', []);
        $matchedHost = false;

        foreach ($sites as $key => $site) {
            $hosts = array_map('strtolower', $site['hosts'] ?? []);
            if (in_array($host, $hosts, true)) {
                $resolvedKey = $key;
                $resolvedSite = $site;
                $matchedHost = true;
                break;
            }
        }

        if (!$matchedHost && config('sites.strict_hosts', false) && app()->environment('production')) {
            abort(404);
        }

        // Resolve the admin's persisted site cookie before route model binding runs.
        // This cookie is only honored for protected admin URLs, never for public APIs.
        $isAdminPath = $request->is('admin') || $request->is('admin/*');
        $selectedAdminSite = (string) $request->cookie('admin_site', '');
        if ($isAdminPath && $selectedAdminSite !== '' && isset($sites[$selectedAdminSite])) {
            $resolvedKey = $selectedAdminSite;
            $resolvedSite = $sites[$selectedAdminSite];
        }

        config([
            'sites.current_key' => $resolvedKey,
            'sites.current' => array_merge($resolvedSite, ['key' => $resolvedKey]),
        ]);

        return $next($request);
    }
}
