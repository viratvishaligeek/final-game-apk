<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CommunityAuthController extends Controller
{
    public function loginForm(): View
    {
        return view('frontend.community.auth', ['mode' => 'login']);
    }

    public function registerForm(): View
    {
        return view('frontend.community.auth', ['mode' => 'register']);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:10'],
            'password' => ['required', 'string', 'max:200'],
        ]);
        $phone = preg_replace('/[\s-]/', '', trim($data['phone'])) ?? '';

        if (!Auth::guard('web')->attempt(['phone' => $phone, 'password' => $data['password'], 'status' => 'active'], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['phone' => 'The supplied credentials are invalid or the account is inactive.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('community.index'));
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge(['phone' => preg_replace('/[\s-]/', '', trim((string) $request->input('phone'))) ?? '']);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:200'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'max:200', 'confirmed'],
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('community.create')->with('success', 'Your account is ready. You can now publish a Guessing post.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('community.index');
    }
}
