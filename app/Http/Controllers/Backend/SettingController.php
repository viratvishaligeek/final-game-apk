<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function create()
    {
        $setting = Setting::get();
        $pageName = 'Global Settings';
        return view('backend.settings', compact('setting', 'pageName'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Add your validation rules for image
                'site_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Add your validation rules for image
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

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
                } elseif ($key == 'site_favicon' && $request->hasFile('site_favicon')) {
                    $logoFav = $request->file('site_favicon');
                    $logoFavName = time() . '_' . $logoFav->getClientOriginalName();
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
            return redirect()->back()->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings. ' . $e->getMessage());
        }
    }
}
