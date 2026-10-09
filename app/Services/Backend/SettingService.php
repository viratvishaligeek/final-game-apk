<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingService
{
    public function getAll()
    {
        return Setting::get();
    }

    public function updateSettings(Request $request): void
    {
        foreach ($request->all() as $key => $value) {
            if ($key == 'site_logo' && $request->hasFile('site_logo')) {
                $logo = $request->file('site_logo');
                $logoName = time() . '_' . $logo->getClientOriginalName();
                $logoPath = public_path('logos');

                if (!file_exists($logoPath)) {
                    mkdir($logoPath, 0775, true);
                }

                $logo->move($logoPath, $logoName);

                Setting::updateOrCreate(
                    ['option' => 'site_logo'],
                    ['value' => $logoName]
                );
            } elseif (
                $key == 'site_favicon'
                && $request->hasFile('site_favicon')
            ) {
                $logoFav = $request->file('site_favicon');
                $logoFavName = time() . '_'
                    . $logoFav->getClientOriginalName();
                $logoFavPath = public_path('logos');

                if (!file_exists($logoFavPath)) {
                    mkdir($logoFavPath, 0775, true);
                }

                $logoFav->move($logoFavPath, $logoFavName);

                Setting::updateOrCreate(
                    ['option' => 'site_favicon'],
                    ['value' => $logoFavName]
                );
            } else {
                Setting::updateOrCreate(
                    ['option' => $key],
                    ['value' => $value]
                );
            }
        }

        app(AppSettingsService::class)->forgetCache();
    }
}
