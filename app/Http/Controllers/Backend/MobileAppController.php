<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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

    public function contactDetails()
    {
        return view('backend.mobile-app.contact-details', [
            'pageName' => 'Contact Details',
            'settings' => $this->getSettings([
                'contact_phone',
                'contact_whatsapp',
                'contact_telegram',
                'contact_email',
                'contact_address',
            ]),
        ]);
    }

    public function updateContactDetails(Request $request)
    {
        $validated = $request->validate([
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_whatsapp' => ['nullable', 'string', 'max:30'],
            'contact_telegram' => ['nullable', 'string', 'max:100'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:500'],
        ]);

        $this->saveSettings($validated);

        return back()->with('success', 'Contact details updated successfully.');
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
                'min_bid_amount',
                'max_bid_amount',
                'add_money_notice',
                'withdraw_money_notice',
                'api_key',
                'webhook_url',
                'payment_bar_code',
            ]),
        ]);
    }

    public function updateLimits(Request $request)
    {
        $validated = $request->validate([
            'min_deposit' => ['nullable', 'numeric', 'min:0'],
            'max_deposit' => ['nullable', 'numeric', 'min:0'],

            'min_withdraw' => ['nullable', 'numeric', 'min:0'],
            'max_withdraw' => ['nullable', 'numeric', 'min:0'],

            'min_bid_amount' => ['nullable', 'numeric', 'min:0'],
            'max_bid_amount' => ['nullable', 'numeric', 'min:0'],

            'add_money_notice' => ['nullable', 'string', 'max:5000'],
            'withdraw_money_notice' => ['nullable', 'string', 'max:5000'],

            'api_key' => ['nullable', 'string', 'max:500'],
            'webhook_url' => ['nullable', 'url', 'max:1000'],

            'payment_bar_code' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $settings = collect($validated)
            ->except('payment_bar_code')
            ->toArray();

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
