<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\AppSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationSoundController extends Controller
{
    private const EVENTS = ['add_money', 'withdraw', 'debit', 'credit', 'winning', 'result', 'chat'];

    public function __construct(private AppSettingsService $settings) {}

    public function index(): View
    {
        $sounds = [];
        foreach (self::EVENTS as $event) {
            $sounds[$event] = (string) $this->settings->value('notification_sound_' . $event, 'default');
        }

        return view('backend.notification-sounds.index', compact('sounds'));
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (self::EVENTS as $event) {
            $rules['notification_sound_' . $event] = ['required', 'in:default,mute'];
        }
        $validated = $request->validate($rules);
        $this->settings->save($validated);

        return redirect()->route('admin.notification-sounds.index')->with('success', 'Notification sound settings saved.');
    }
}
