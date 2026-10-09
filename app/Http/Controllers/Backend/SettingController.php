<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function __construct(
        private SettingService $settingService
    ) {}

    public function create()
    {
        $setting = $this->settingService->getAll();
        $pageName = 'Global Settings';

        return view('backend.settings', compact('setting', 'pageName'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'site_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with(
                    'error',
                    $validator->errors()->first()
                );
            }

            $this->settingService->updateSettings($request);

            return redirect()->back()->with(
                'success',
                'Settings updated successfully.'
            );
        } catch (\Exception $e) {
            return redirect()->back()->with(
                'error',
                'Failed to update settings. ' . $e->getMessage()
            );
        }
    }
}
