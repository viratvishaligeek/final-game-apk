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
            'site_description' => ['nullable', 'string', 'max:1000'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'homepage_heading_line1' => ['nullable', 'string', 'max:120'],
            'homepage_heading_line2' => ['nullable', 'string', 'max:120'],
            'homepage_heading_line3' => ['nullable', 'string', 'max:120'],
            'homepage_intro' => ['nullable', 'string', 'max:2000'],
            'announcement_text' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:1000'],
            'robots_default' => ['nullable', 'string', 'in:index,follow,noindex,follow,index,nofollow,noindex,nofollow'],
            'twitter_card' => ['nullable', 'string', 'in:summary,summary_large_image'],
            'app_download_url' => ['nullable', 'url', 'max:1000'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
            'footer_description' => ['nullable', 'string', 'max:1000'],
            'contact_phone' => ['nullable', 'string', 'max:100'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'social_facebook' => ['nullable', 'url', 'max:500'],
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_youtube' => ['nullable', 'url', 'max:500'],
            'social_telegram' => ['nullable', 'url', 'max:500'],
            'ticker_text' => ['nullable', 'string', 'max:1000'],
            'disclaimer_content' => ['nullable', 'string', 'max:10000'],
            'chart_chunk_size' => ['nullable', 'integer', 'min:1', 'max:50'],
            'referral_percentage' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'referral_min_amount' => ['sometimes', 'required', 'numeric', 'min:0', 'max:1000000'],
            'site_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'site_favicon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'og_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
        ]);

        $newFiles = [];
        $oldFiles = [];

        try {
            DB::transaction(function () use ($request, $validated, &$newFiles, &$oldFiles) {
                foreach (['title', 'email', 'site_description', 'site_tagline', 'homepage_heading_line1', 'homepage_heading_line2', 'homepage_heading_line3', 'homepage_intro', 'announcement_text', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_title', 'og_description', 'robots_default', 'twitter_card', 'app_download_url', 'copyright_text', 'footer_description', 'contact_phone', 'contact_address', 'social_facebook', 'social_instagram', 'social_youtube', 'social_telegram', 'ticker_text', 'disclaimer_content', 'chart_chunk_size', 'referral_percentage', 'referral_min_amount'] as $key) {
                    if (array_key_exists($key, $validated)) {
                        Setting::updateOrCreate(
                            ['option' => $key],
                            ['value' => $validated[$key]]
                        );
                    }
                }

                foreach (['site_logo', 'site_favicon', 'og_image'] as $key) {
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
