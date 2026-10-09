<?php

namespace App\\Http\\Controllers\\Backend\\User;

use App\\Http\\Controllers\\Controller;
use App\\Models\\User;
use App\\Services\\WalletService;
use Illuminate\\Http\\Request;
use Illuminate\\Validation\\ValidationException;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    public function credit(Request $request, string $id)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000000'],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $user = User::query()->findOrFail($id);

            $this->walletService->credit(
                $user,
                (float) $validated['amount'],
                $validated['remark'] ?? null
            );

            return back()->with(
                'success',
                '₹' . number_format((float) $validated['amount'], 2)
                    . ' credited successfully.'
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Unable to credit wallet. Please try again.'
            );
        }
    }

    public function debit(Request $request, string $id)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000000'],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $user = User::query()->findOrFail($id);

            $this->walletService->debit(
                $user,
                (float) $validated['amount'],
                $validated['remark'] ?? null
            );

            return back()->with(
                'success',
                '₹' . number_format((float) $validated['amount'], 2)
                    . ' debited successfully.'
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Unable to debit wallet. Please try again.'
            );
        }
    }
}
