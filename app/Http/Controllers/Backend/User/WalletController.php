<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
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
        try {
            $validated = $request->validate([
                'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000000'],
                'remark' => ['nullable', 'string', 'max:255'],
                'idempotency_key' => ['nullable', 'uuid'],
            ]);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please correct the highlighted fields.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
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

            if ($idempotencyKey) {
                $existing = Transaction::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing) {
                    return $this->creditJsonResponse($request, $user, $existing, $amount);
                }
            }

            $transaction = $this->walletService->credit(
                $user,
                $amount,
                $validated['remark'] ?? null,
                [
                    'admin_id' => auth('admin')->id(),
                    'idempotency_key' => $idempotencyKey,
                ]
            );

            // A UUID can only credit once. Also reject attempts to reuse an
            // existing key for a different user or amount.
            if (
                (int) $transaction->user_id !== (int) $user->id
                || round((float) $transaction->amount, 2) !== $amount
                || $transaction->type !== 'credit'
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'This submission token has already been used for a different credit.',
                ], 409);
            }

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

            return back()->with(
                'success',
                '₹' . number_format($amount, 2) . ' credited successfully.'
            );
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'The credit request could not be validated.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            // A concurrent retry may lose the unique-key race after the first
            // request has committed. Resolve it as the same successful request.
            if ($idempotencyKey) {
                $existing = Transaction::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing) {
                    $user = User::query()->find($id);

                    if ($user) {
                        return $this->creditJsonResponse(
                            $request,
                            $user,
                            $existing,
                            round((float) $validated['amount'], 2)
                        );
                    }
                }
            }

            report($e);

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
                $validated['remark'] ?? null
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
