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
                'about', 'about-us', 'contact', 'whatsapp', 'faq', 'privacy-policy',
                'terms-and-conditions', 'terms-conditions', 'disclaimer',
            ];

            $pages = request()->attributes->get('frontend_nav_pages');
            if ($pages === null) {
                $pages = Page::query()
                    ->where('status', 'active')
                    ->where('menu_visible', true)
                    ->whereNotIn('slug', $reservedSlugs)
                    ->orderBy('menu_order')
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']);
                request()->attributes->set('frontend_nav_pages', $pages);
            }

            $view->with('navPages', $pages);
        });
    }
}
