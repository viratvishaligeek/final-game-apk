<?php

namespace App\Providers;

use App\Models\Page;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['frontend.include.header', 'frontend.include.footer'], function ($view) {
            $reservedSlugs = [
                'about', 'contact', 'faq', 'privacy-policy',
                'terms-and-conditions', 'disclaimer',
            ];

            $pages = Page::query()
                ->where('status', 'active')
                ->where('menu_visible', true)
                ->whereNotIn('slug', $reservedSlugs)
                ->orderBy('menu_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

            $view->with('navPages', $pages);
        });
    }
}
