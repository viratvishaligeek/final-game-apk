<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}


    public function credit(Request $request, string $id)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        try {

            $user = User::findOrFail($id);
            $this->walletService->credit($user, (float) $validated['amount'], $validated['remark'] ?? null);

            return back()->with(
                'success',
                '₹' . number_format($validated['amount'], 2)
                    . ' credited successfully.'
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to credit wallet. Please try again.' . $e->getMessage());
        }
    }

    public function debit(Request $request, string $id)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $user = User::findOrFail($id);
            $this->walletService->debit(
                $user,
                (float) $validated['amount'],
                $validated['remark'] ?? null
            );
            return back()->with(
                'success',
                '₹' . number_format($validated['amount'], 2)
                    . ' debited successfully.'
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Unable to debit wallet. Please try again.' . $e->getMessage());
        }
    }
}
