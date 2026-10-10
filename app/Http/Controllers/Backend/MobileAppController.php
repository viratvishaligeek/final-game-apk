<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class MobileAppController extends Controller
{
    public function marque()
    {
        return view('backend.mobile-app.marque', [
            'pageName' => 'Update Marquee',
            'settings' => $this->getSettings([
                'marquee',
            ]),
        ]);
    }

    public function updateMarque(Request $request)
    {
        $validated = $request->validate([
            'marquee' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->saveSettings($validated);

        return back()->with('success', 'Marquee updated successfully.');
    }

    public function limits()
    {
        return view('backend.mobile-app.limits', [
            'pageName' => 'Transaction & Payment Limits',
            'settings' => $this->getSettings([
                'min_deposit',
                'max_deposit',
                'min_withdraw',
                'max_withdraw',
                'min_bid_amount_jodi',
                'max_bid_amount_jodi',
                'min_bid_amount_haruf',
                'max_bid_amount_haruf',
                'add_money_notice',
                'withdraw_money_notice',
                'api_key',
                'webhook_url',
                'payment_bar_code',
                'manual_upi_id',
                'manual_upi_name',
            ]),
        ]);
    }

    public function updateLimits(Request $request)
    {
        $validated = $request->validate([
            'min_deposit' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],
            'max_deposit' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],

            'min_withdraw' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],
            'max_withdraw' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],

            'min_bid_amount_jodi' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],
            'max_bid_amount_jodi' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],
            'min_bid_amount_haruf' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],
            'max_bid_amount_haruf' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],

            'add_money_notice' => ['nullable', 'string', 'max:5000'],
            'withdraw_money_notice' => ['nullable', 'string', 'max:5000'],

            'api_key' => ['nullable', 'string', 'max:500'],
            'webhook_url' => ['nullable', 'url', 'max:1000'],
            'manual_upi_id' => ['nullable', 'string', 'max:1000'],
            'manual_upi_name' => ['nullable', 'string', 'max:1000'],

            'payment_bar_code' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        foreach (
            [
                ['min_deposit', 'max_deposit'],
                ['min_withdraw', 'max_withdraw'],
                ['min_bid_amount_jodi', 'max_bid_amount_jodi'],
                ['min_bid_amount_haruf', 'max_bid_amount_haruf'],
            ] as [$minimumKey, $maximumKey]
        ) {
            $minimum = (float) ($validated[$minimumKey] ?? 0);
            $maximum = (float) ($validated[$maximumKey] ?? 0);

            if ($maximum > 0 && $minimum > $maximum) {
                throw ValidationException::withMessages([
                    $maximumKey => ["{$maximumKey} must be greater than or equal to {$minimumKey}."],
                ]);
            }
        }

        $settings = collect($validated)
            ->except('payment_bar_code')
            ->toArray();

        // Blank password input means "keep the existing gateway key".
        if (!$request->filled('api_key')) {
            unset($settings['api_key']);
        }

        DB::transaction(function () use ($settings, $request) {
            $this->saveSettings($settings);

            if ($request->hasFile('payment_bar_code')) {
                $this->savePaymentBarcode(
                    $request->file('payment_bar_code')
                );
            }
        });

        return back()->with(
            'success',
            'Transaction and payment settings updated successfully.'
        );
    }

    public function notice()
    {
        return view('backend.mobile-app.notice', [
            'pageName' => 'Admin Notice',
            'settings' => $this->getSettings([
                'admin_notice',
                'notice_status',
            ]),
        ]);
    }

    public function updateNotice(Request $request)
    {
        $validated = $request->validate([
            'admin_notice' => ['nullable', 'string', 'max:5000'],
            'notice_status' => ['required', 'in:active,inactive'],
        ]);

        $this->saveSettings($validated);

        return back()->with(
            'success',
            'Admin notice updated successfully.'
        );
    }

    private function getSettings(array $keys): array
    {
        return Setting::query()
            ->whereIn('option', $keys)
            ->pluck('value', 'option')
            ->toArray();
    }

    private function saveSettings(array $settings): void
    {
        foreach ($settings as $option => $value) {
            Setting::updateOrCreate(
                ['option' => $option],
                ['value' => $value]
            );
        }
        app(\App\Services\AppSettingsService::class)->forgetCache();
    }

    private function savePaymentBarcode($file): void
    {
        $directory = public_path('uploads/payment');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $oldBarcode = Setting::query()
            ->where('option', 'payment_bar_code')
            ->value('value');

        if ($oldBarcode) {
            $oldPath = $directory . '/' . $oldBarcode;

            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        }

        $filename = 'payment_barcode_' . uniqid() . '.' .
            $file->getClientOriginalExtension();

        $file->move($directory, $filename);

        Setting::updateOrCreate(
            ['option' => 'payment_bar_code'],
            ['value' => $filename]
        );
    }

    public function liveChat()
    {
        // code will be here
    }
}
