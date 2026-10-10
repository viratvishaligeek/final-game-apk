<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\AppSettingsService;
use Illuminate\Database\Seeder;

class PlayOnlineKhaiwalSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'title' => 'Play Online Khaiwal',
            'site_tagline' => 'SATTA KING RESULTS • MONTHLY CHARTS • HISTORICAL RECORDS',
            'site_description' => 'Play Online Khaiwal provides a clear reference for Satta King results, Satta Matka market records, and game-wise monthly result charts. Browse saved historical data by market and month.',
            'meta_title' => 'Play Online Khaiwal | Satta King Results & Monthly Charts',
            'meta_description' => 'Browse Satta King results, Satta Matka market records, and game-wise monthly result charts on Play Online Khaiwal. View saved results by game, date, month, and year.',
            'meta_keywords' => 'Satta King, Online Khaiwal, Satta Matka, Satta King Result, Satta Result Chart, Satta King Monthly Chart',
            'og_title' => 'Play Online Khaiwal | Satta King Results & Monthly Charts',
            'og_description' => 'Game-wise Satta King result records and monthly charts, organized by date and market.',
            'robots_default' => 'index,follow',
            'twitter_card' => 'summary_large_image',
            'canonical_url' => 'https://playonlinekhaiwal.com',
            'homepage_heading_line1' => 'Every market.',
            'homepage_heading_line2' => 'Every record.',
            'homepage_heading_line3' => 'One clear board.',
            'homepage_intro' => 'Browse stored Satta King results, compare recent market records, and open game-wise monthly charts. Missing values are marked as pending, never predicted.',
            'footer_description' => 'Play Online Khaiwal organizes stored market results and historical monthly charts for reference. Historical records do not predict future outcomes.',
            'disclaimer_content' => 'Results and historical charts are provided for information only and reflect stored records. They are not predictions, guarantees, or financial advice. Follow applicable local laws and use this information responsibly.',
            'copyright_text' => '© ' . date('Y') . ' Play Online Khaiwal',
            'chart_chunk_size' => '10',
        ];

        $knownLegacyDefaults = [
            'site_tagline' => ['RESULTS • RECORDS • CHARTS'],
            'site_description' => ['A structured reference for published market results, schedules, and historical records.'],
            'meta_title' => ['Satta 786 Results Today, Market Board & Historical Charts', 'Satta 786 Results & Historical Charts'],
            'meta_description' => ['Browse published market results, compare today and yesterday, and open market-wise historical charts and year-wise records.', 'Browse published market results, schedules, and historical result charts by market and year.'],
            'footer_description' => ['A structured reference for published market results, schedules, and historical records.'],
        ];

        foreach ($defaults as $option => $value) {
            // Brand and SEO defaults are intentional project defaults. Existing
            // operator-entered values are preserved, except legacy identity and
            // metadata values explicitly tied to the former placeholder brand.
            $setting = Setting::query()->where('option', $option)->first();

            if (!$setting) {
                Setting::create(['option' => $option, 'value' => $value]);
                continue;
            }

            $current = trim((string) $setting->value);
            $isLegacyBrand = ($option === 'canonical_url' && $current !== $value)
                || (in_array($option, [
                'title', 'site_tagline', 'site_description', 'meta_title',
                'meta_description', 'meta_keywords', 'og_title', 'og_description', 'footer_description',
                'copyright_text',
            ], true) && (
                str_contains(strtolower($current), 'satta 786')
                || str_contains(strtolower($current), 'example.com')
                || str_contains(strtolower($current), 'placeholder')
                || in_array($current, $knownLegacyDefaults[$option] ?? [], true)
            ));

            if ($isLegacyBrand) {
                $setting->update(['value' => $value]);
            }
        }

        // These are intentionally empty until the operator configures real URLs.
        Setting::firstOrCreate(['option' => 'app_download_url'], ['value' => '']);
        Setting::firstOrCreate(['option' => 'social_facebook'], ['value' => '']);
        Setting::firstOrCreate(['option' => 'social_instagram'], ['value' => '']);
        Setting::firstOrCreate(['option' => 'social_youtube'], ['value' => '']);
        Setting::firstOrCreate(['option' => 'social_telegram'], ['value' => '']);

        app(AppSettingsService::class)->forgetCache();
    }
}
