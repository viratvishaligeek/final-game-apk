<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AppSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SettingController extends Controller
{
    public function create()
    {
        $setting = Setting::query()->get();
        $pageName = 'Global Settings';

        return view('backend.settings', compact('setting', 'pageName'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'referral_percentage' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'referral_min_amount' => ['sometimes', 'required', 'numeric', 'min:0', 'max:1000000'],
            'site_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'site_favicon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $newFiles = [];
        $oldFiles = [];

        try {
            DB::transaction(function () use ($request, $validated, &$newFiles, &$oldFiles) {
                foreach (['title', 'email', 'referral_percentage', 'referral_min_amount'] as $key) {
                    if (array_key_exists($key, $validated)) {
                        Setting::updateOrCreate(
                            ['option' => $key],
                            ['value' => $validated[$key]]
                        );
                    }
                }

                foreach (['site_logo', 'site_favicon'] as $key) {
                    if (!$request->hasFile($key)) {
                        continue;
                    }

                    $file = $request->file($key);
                    $filename = $key . '_' . Str::uuid() . '.' . $file->extension();
                    $directory = public_path('logos');

                    File::ensureDirectoryExists($directory, 0755, true);
                    $file->move($directory, $filename);
                    $newFiles[] = $directory . DIRECTORY_SEPARATOR . $filename;

                    $oldFilename = Setting::query()
                        ->where('option', $key)
                        ->value('value');

                    if (is_string($oldFilename) && $oldFilename !== '') {
                        $oldFiles[] = $directory . DIRECTORY_SEPARATOR . basename($oldFilename);
                    }

                    Setting::updateOrCreate(
                        ['option' => $key],
                        ['value' => $filename]
                    );
                }
            });

            app(AppSettingsService::class)->forgetCache();

            foreach ($oldFiles as $oldFile) {
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
            }

            return redirect()->back()->with('success', 'Settings updated successfully.');
        } catch (Throwable $e) {
            foreach ($newFiles as $newFile) {
                if (File::exists($newFile)) {
                    File::delete($newFile);
                }
            }

            Log::error('Global settings update failed.', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to update settings. Please try again.');
        }
    }
}
