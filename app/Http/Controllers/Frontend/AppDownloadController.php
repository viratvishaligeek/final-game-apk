<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\AppSettingsService;
use Illuminate\Http\RedirectResponse;

class AppDownloadController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $destination = trim((string) app(AppSettingsService::class)->value('app_download_url', ''));
        $scheme = $destination !== '' ? parse_url($destination, PHP_URL_SCHEME) : null;

        if (filter_var($destination, FILTER_VALIDATE_URL) && in_array(strtolower((string) $scheme), ['https', 'http'], true)) {
            return redirect()->away($destination);
        }

        return redirect()
            ->route('information', ['page' => 'contact'])
            ->with('notice', 'The app download link has not been configured yet. Please contact the site administrator.');
    }
}
