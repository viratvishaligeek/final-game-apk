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

        $matched = false;
        foreach ($sites as $key => $site) {
            $hosts = array_map('strtolower', $site['hosts'] ?? []);
            if (in_array($host, $hosts, true)) {
                $resolvedKey = $key;
                $resolvedSite = $site;
                $matched = true;
                break;
            }
        }

        if (!$matched && config('sites.strict_hosts', false) && app()->environment('production')) {
            abort(404);
        }

        config([
            'sites.current_key' => $resolvedKey,
            'sites.current' => array_merge($resolvedSite, ['key' => $resolvedKey]),
        ]);

        return $next($request);
    }
}
