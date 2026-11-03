<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Services\WalletService;
use App\Services\Admin\AdminWalletCreditService;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected AdminWalletCreditService $adminWalletCreditService
    ) {}

    public function credit(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000000'],
                'remark' => ['nullable', 'string', 'max:255'],
                'idempotency_key' => ['nullable', 'uuid'],
            ]);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please correct the highlighted fields.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            throw $exception;
        }

        $idempotencyKey = $validated['idempotency_key'] ?? null;
        if ($request->expectsJson() && !$idempotencyKey) {
            return response()->json([
                'success' => false,
                'message' => 'This credit request is missing its submission token. Please reopen the modal and try again.',
            ], 422);
        }

        try {
            $user = User::query()->findOrFail($id);
            $amount = round((float) $validated['amount'], 2);
            $credit = $this->adminWalletCreditService->credit(
                $user,
                $amount,
                $validated['remark'] ?? null,
                $idempotencyKey,
                auth('admin')->id()
            );

            if ($credit['duplicate'] && $request->expectsJson()) {
                return $this->creditJsonResponse($request, $user, $credit['transaction'], $amount);
            }

            $transaction = $credit['transaction'];
            $user->refresh();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '₹' . number_format($amount, 2) . ' credited successfully.',
                    'data' => [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'balance' => round((float) $user->balance, 2),
                        'transaction_id' => $transaction->id,
                        'amount' => $amount,
                        'previous_balance' => $transaction->previous_balance,
                    ],
                ]);
            }

            return back()->with('success', '₹' . number_format($amount, 2) . ' credited successfully.');
        } catch (ConflictHttpException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 409);
            }

            return back()->with('error', $exception->getMessage());
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage() ?: 'The credit request could not be validated.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            return back()->withErrors($exception->errors())->withInput();
        } catch (\\Throwable $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to credit wallet. No balance change was confirmed. Please try again.',
                ], 500);
            }

            return back()->with('error', 'Unable to credit wallet. Please try again.');
        }
    }

    private function creditJsonResponse(
        Request $request,
        User $user,
        Transaction $transaction,
        float $requestedAmount
    ) {
        if (
            (int) $transaction->user_id !== (int) $user->id
            || round((float) $transaction->amount, 2) !== $requestedAmount
            || $transaction->type !== 'credit'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'This submission token has already been used for a different credit.',
            ], 409);
        }

        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => '₹' . number_format($requestedAmount, 2) . ' credited successfully.',
            'duplicate' => true,
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'balance' => round((float) $user->balance, 2),
                'transaction_id' => $transaction->id,
                'amount' => $requestedAmount,
                'previous_balance' => $transaction->previous_balance,
            ],
        ]);
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
                $validated['remark'] ?? null,
                true,
                ['admin_id' => auth('admin')->id()]
            );

            return back()->with(
                'success',
                '₹' . number_format((float) $validated['amount'], 2)
                    . ' debited successfully.'
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Unable to debit wallet. Please try again.'
            );
        }
    }
}
