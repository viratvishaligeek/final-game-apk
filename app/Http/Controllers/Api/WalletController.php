<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletRequest;
use App\Services\AppSettingsService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected AppSettingsService $settings
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min(
            max((int) $request->input('per_page', 15), 5),
            50
        );
        $page = max(
            (int) $request->input('page', 1),
            1
        );
        $type = strtolower(
            trim((string) $request->input('type', 'all'))
        );
        if (!in_array($type, ['all', 'credit', 'debit'], true)) {
            $type = 'all';
        }

        $successfulStatuses = [
            'completed',
            'success',
            'successful',
        ];

        $transactionQuery = Transaction::query()
            ->where('user_id', $user->id);
        if ($type !== 'all') {
            $transactionQuery->where('type', $type);
        }
        $transactions = $transactionQuery
            ->latest('id')
            ->paginate(
                $perPage,
                ['*'],
                'page',
                $page
            );

        $totalCredited = Transaction::query()
            ->where('user_id', $user->id)
            ->whereRaw('LOWER(type) = ?', ['credit'])
            ->whereIn('status', $successfulStatuses)
            ->where(function ($query) {
                $query
                    ->whereRaw('LOWER(subject) LIKE ?', ['%add money%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%added money%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%deposit%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%Automated UPI gateway%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%cash added%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%wallet top%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%top up%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%topup%']);
            })
            ->sum('amount');

        $totalDebited = Transaction::query()
            ->where('user_id', $user->id)
            ->whereRaw('LOWER(type) = ?', ['debit'])
            ->whereIn('status', $successfulStatuses)
            ->where(function ($query) {
                $query
                    ->whereRaw('LOWER(subject) LIKE ?', ['%withdraw%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%withdrawal%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%bank transfer%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%cash out%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%payout%']);
            })
            ->sum('amount');

        $totalPlayedBet = Bid::query()
            ->where('user_id', $user->id)
            ->sum('amount');

        $totalWin = Bid::query()
            ->where('user_id', $user->id)
            ->whereNotNull('winning_amount')
            ->where('winning_amount', '>', 0)
            ->sum('winning_amount');

        $transactionData = collect($transactions->items())
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'txn_id' => 'TXN' . str_pad(
                        $transaction->id,
                        8,
                        '0',
                        STR_PAD_LEFT
                    ),
                    'description' => $transaction->subject
                        ?: 'Wallet Transaction',
                    'type' => strtolower(
                        (string) $transaction->type
                    ),
                    'amount' => round(
                        (float) $transaction->amount,
                        2
                    ),
                    'balance' => round(
                        (float) ($transaction->balance ?? 0),
                        2
                    ),
                    'status' => $this->normalizeTransactionStatus(
                        $transaction->status
                    ),
                    'created_at' => $transaction->created_at,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Wallet data fetched successfully.',
            'data' => [
                'balance' => round(
                    (float) $user->balance,
                    2
                ),
                'summary' => [
                    'cash_added' => round(
                        (float) $totalCredited,
                        2
                    ),
                    'withdrawn' => round(
                        (float) $totalDebited,
                        2
                    ),
                    'played_bet' => round(
                        (float) $totalPlayedBet,
                        2
                    ),
                    'total_win' => round(
                        (float) $totalWin,
                        2
                    ),
                ],
                'transactions' => [
                    'data' => $transactionData,
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                ],
            ],
        ]);
    }


    /**
     * Normalize database transaction status
     */
    private function normalizeTransactionStatus(
        ?string $status
    ): string {
        $status = strtolower(
            (string) $status
        );
        return match ($status) {
            'completed',
            'success',
            'successful' => 'success',
            'pending',
            'processing' => 'pending',
            'failed',
            'failure' => 'failed',
            'rejected',
            'cancelled',
            'canceled' => 'rejected',
            default => $status ?: 'pending',
        };
    }

    /**
     * Manual UPI information.
     */
    public function paymentMethods()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'manual_upi' => [
                    'upi_id' => config('services.manual_upi.upi_id'),
                    'name' => config('services.manual_upi.name'),
                    'qr_url' => config('services.manual_upi.qr_url'),
                ],
                'gateway' => [
                    'enabled' => filled(config('services.upi_gateway.key')),
                ],
            ],
        ]);
    }

    /**
     * Manual UPI Add Money Request.
     */
    public function addMoneyRequest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:1000000'],
            'utr' => ['required', 'string', 'max:100', 'unique:wallet_requests,utr'],
            'screenshot' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);
        $user = $request->user();
        $amount = (float) $validated['amount'];

        $this->settings->validateRange(
            $amount,
            'min_deposit',
            'max_deposit',
            'amount'
        );
        $path = $request->file('screenshot')->store(
            'wallet/manual-payments',
            'public'
        );
        try {
            $walletRequest = DB::transaction(function () use ($user, $validated, $path) {
                User::query()->lockForUpdate()->findOrFail($user->id);

                $pendingExists = WalletRequest::query()
                    ->where('user_id', $user->id)
                    ->where('request_type', 'credit')
                    ->where('payment_method', 'manual_upi')
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->exists();

                if ($pendingExists) {
                    throw ValidationException::withMessages([
                        'amount' => 'A similar payment request is already pending.',
                    ]);
                }

                return WalletRequest::create([
                    'user_id' => $user->id,
                    'request_type' => 'credit',
                    'payment_method' => 'manual_upi',
                    'amount' => $validated['amount'],
                    'utr' => $validated['utr'],
                    'screenshot' => $path,
                    'status' => 'pending',
                    'remark' => 'Manual UPI wallet top-up',
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
        return response()->json([
            'success' => true,
            'message' => 'Payment submitted successfully. Admin will verify your payment.',
            'data' => [
                'id' => $walletRequest->id,
                'status' => $walletRequest->status,
            ],
        ], 201);
    }

    public function createGatewayOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:1000000'],
        ]);
        $user = $request->user();
        $clientTxnId = 'WLT-' .
            now()->format('YmdHis') .
            '-' .
            strtoupper(Str::random(8));
        $amount = (float) $validated['amount'];

        $this->settings->validateRange(
            $amount,
            'min_deposit',
            'max_deposit',
            'amount'
        );
        $walletRequest = DB::transaction(function () use ($user, $clientTxnId, $validated) {
            User::query()->lockForUpdate()->findOrFail($user->id);

            $pendingExists = WalletRequest::query()
                ->where('user_id', $user->id)
                ->where('request_type', 'credit')
                ->where('payment_method', 'gateway')
                ->whereIn('status', ['pending', 'processing'])
                ->lockForUpdate()
                ->exists();

            if ($pendingExists) {
                throw ValidationException::withMessages([
                    'amount' => 'A similar payment request is already pending.',
                ]);
            }

            return WalletRequest::create([
                'user_id' => $user->id,
                'request_type' => 'credit',
                'payment_method' => 'gateway',
                'amount' => $validated['amount'],
                'status' => 'processing',
                'client_txn_id' => $clientTxnId,
                'remark' => 'Automated UPI gateway payment',
            ]);
        });
        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post(
                    config('services.upi_gateway.create_order_url'),
                    [
                        'key' => config('services.upi_gateway.key'),
                        'client_txn_id' => $clientTxnId,
                        'amount' => $validated['amount'],
                        'p_info' => 'Wallet Top-up',
                        'customer_name' => $user->name,
                        'customer_email' => $user->email ?? 'noemail@gmail.com',
                        'customer_mobile' => $user->phone,
                        'redirect_url' => config(
                            'services.upi_gateway.return_url'
                        ),
                        'webhook_url' => config('services.upi_gateway.webhook_url'),
                        'udf1' => (string) $user->id,
                        'udf2' => 'wallet',
                        'udf3' => (string) $walletRequest->id,
                    ]
                );
            if (!$response->successful()) {
                $walletRequest->update([
                    'status' => 'failed',
                    'gateway_status' => 'gateway_error',
                    'remark' => 'Gateway order creation failed.',
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to create payment order.',
                ], 422);
            }
            $result = $response->json();
            if (($result['status'] ?? false) !== true) {
                $walletRequest->update([
                    'status' => 'failed',
                    'gateway_status' => 'order_creation_failed',
                    'remark' => $result['msg'] ?? 'Gateway rejected order.',
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $result['msg'] ?? 'Payment gateway rejected the request.',
                ], 422);
            }
            return response()->json([
                'success' => true,
                'message' => 'Payment order created.',
                'data' => [
                    'request_id' => $walletRequest->id,
                    'client_txn_id' => $clientTxnId,
                    'payment_url' => $result['data']['payment_url'] ?? null,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            $walletRequest->update([
                'status' => 'failed',
                'gateway_status' => 'exception',
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway is temporarily unavailable.',
            ], 500);
        }
    }

    public function gatewayReturn(Request $request): JsonResponse
    {
        $clientTxnId = $request->query('client_txn_id');
        if (!$clientTxnId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction.',
            ], 422);
        }
        $walletRequest = WalletRequest::query()
            ->where('client_txn_id', $clientTxnId)
            ->first();
        if (!$walletRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }
        $this->verifyGatewayTransaction($walletRequest);
        $walletRequest->refresh();
        return response()->json([
            'success' => $walletRequest->status === 'approved',
            'message' => match ($walletRequest->status) {
                'approved' => 'Payment successful and wallet credited.',
                'failed' => 'Payment failed.',
                default => 'Payment is being verified.',
            },
            'data' => [
                'status' => $walletRequest->status,
                'request_id' => $walletRequest->id,
            ],
        ]);
    }

    /**
     * Gateway webhook.
     */
    public function gatewayWebhook(Request $request): JsonResponse
    {
        $clientTxnId = trim((string) $request->input('client_txn_id', ''));

        if ($clientTxnId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Missing transaction ID.',
            ], 422);
        }
        $walletRequest = WalletRequest::query()
            ->where('client_txn_id', $clientTxnId)
            ->first();
        if (!$walletRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }

        // Never trust the status or amount supplied by the callback sender.
        try {
            $verified = $this->verifyGatewayTransaction($walletRequest);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify payment status. Please retry.',
            ], 503);
        }

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'Payment could not be verified yet. Please retry.',
            ], 503);
        }

        $walletRequest->refresh();

        return response()->json([
            'success' => true,
            'status' => $walletRequest->status,
        ]);
    }

    /**
     * Verify payment directly with the provider. Returns false when the
     * provider response is unavailable or does not match our stored order.
     */
    protected function verifyGatewayTransaction(
        WalletRequest $walletRequest
    ): bool {
        $response = Http::timeout(30)
            ->acceptJson()
            ->post(
                config('services.upi_gateway.status_url'),
                [
                    'key' => config('services.upi_gateway.key'),
                    'client_txn_id' => $walletRequest->client_txn_id,
                    'txn_date' => ($walletRequest->created_at ?: now())->format('d-m-Y'),
                ]
            );
        if (!$response->successful()) {
            return false;
        }
        $result = $response->json();
        if (($result['status'] ?? false) !== true) {
            return false;
        }

        $data = $result['data'] ?? null;

        if (!is_array($data)) {
            return false;
        }

        $verifiedClientTxnId = (string) ($data['client_txn_id'] ?? '');
        if (
            $verifiedClientTxnId === ''
            || !hash_equals((string) $walletRequest->client_txn_id, $verifiedClientTxnId)
        ) {
            Log::warning('UPI gateway verification returned a mismatched client transaction ID.', [
                'wallet_request_id' => $walletRequest->id,
            ]);

            return false;
        }

        if (
            !isset($data['amount'])
            || !is_numeric($data['amount'])
            || round((float) $data['amount'], 2) !== round((float) $walletRequest->amount, 2)
        ) {
            Log::warning('UPI gateway verification returned an amount mismatch.', [
                'wallet_request_id' => $walletRequest->id,
                'expected_amount' => (float) $walletRequest->amount,
                'verified_amount' => $data['amount'] ?? null,
            ]);

            return false;
        }

        $status = strtolower(trim((string) ($data['status'] ?? '')));

        if (!in_array($status, ['success', 'failure', 'pending', 'processing'], true)) {
            return false;
        }

        $this->applyVerifiedGatewayStatus($walletRequest, $data, $status);

        return true;
    }

    /**
     * Apply a provider-verified status once, under a row lock. The original
     * wallet request status prevents duplicate callbacks from double-crediting.
     */
    private function applyVerifiedGatewayStatus(
        WalletRequest $walletRequest,
        array $data,
        string $status
    ): void {
        DB::transaction(function () use ($walletRequest, $data, $status) {
            $requestRow = WalletRequest::query()
                ->lockForUpdate()
                ->findOrFail($walletRequest->id);

            // Approved is terminal: a delayed failure callback must not undo it.
            if ($requestRow->status === 'approved') {
                return;
            }

            $requestRow->update([
                'gateway_txn_id' => $data['id'] ?? $requestRow->gateway_txn_id,
                'customer_vpa' => $data['customer_vpa'] ?? $requestRow->customer_vpa,
                'gateway_status' => $status,
                'utr' => $data['upi_txn_id'] ?? $requestRow->utr,
            ]);

            if ($status === 'success') {
                if ($requestRow->request_type !== 'credit') {
                    Log::warning('UPI gateway reported success for a non-credit wallet request.', [
                        'wallet_request_id' => $requestRow->id,
                    ]);

                    return;
                }

                $user = User::query()
                    ->lockForUpdate()
                    ->findOrFail($requestRow->user_id);

                $this->walletService->credit(
                    $user,
                    (float) $requestRow->amount,
                    'Wallet top-up via UPI Gateway'
                );

                $requestRow->update([
                    'status' => 'approved',
                    'processed_at' => now(),
                    'admin_remark' => 'Automatically approved after server-side gateway verification.',
                ]);

                return;
            }

            if ($status === 'failure') {
                $requestRow->update([
                    'status' => 'failed',
                ]);
            }
        });
    }

    /**
     * Withdrawal request.
     */
    public function withdraw(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:1000000'],
            'mode' => ['required', 'in:bank,upi'],
            'account_name' => ['required_if:mode,bank', 'nullable', 'string', 'max:150'],
            'account_number' => ['required_if:mode,bank', 'nullable', 'string', 'max:100'],
            'ifsc' => ['required_if:mode,bank', 'nullable', 'string', 'max:20'],
            'upi_id' => ['required_if:mode,upi', 'nullable', 'string', 'max:150'],
            'qr_code_image' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);
        $user = $request->user();
        $amount = round((float) $validated['amount'], 2);
        $this->settings->validateRange(
            $amount,
            'min_withdraw',
            'max_withdraw',
            'amount'
        );

        $qrPath = null;
        if ($request->hasFile('qr_code_image')) {
            $qrPath = $request->file('qr_code_image')
                ->store('wallet/withdrawal-qr', 'public');
        }

        try {
            $walletRequest = DB::transaction(function () use ($user, $validated, $amount, $qrPath) {
                $lockedUser = User::query()
                    ->lockForUpdate()
                    ->findOrFail($user->id);

                $reservedWithdrawals = (float) WalletRequest::query()
                    ->where('user_id', $lockedUser->id)
                    ->where('request_type', 'debit')
                    ->whereIn('status', ['pending', 'processing'])
                    ->sum('amount');

                if ($amount + $reservedWithdrawals > (float) $lockedUser->balance) {
                    throw ValidationException::withMessages([
                        'amount' => 'This withdrawal would exceed your available balance after pending withdrawals.',
                    ]);
                }

                return WalletRequest::create([
                    'user_id' => $lockedUser->id,
                    'request_type' => 'debit',
                    'payment_method' => $validated['mode'],
                    'amount' => $amount,
                    'status' => 'pending',
                    'account_name' => $validated['account_name'] ?? null,
                    'account_number' => $validated['account_number'] ?? null,
                    'ifsc' => strtoupper($validated['ifsc'] ?? '') ?: null,
                    'upi_id' => $validated['upi_id'] ?? null,
                    'qr_code_image' => $qrPath,
                    'remark' => 'Wallet withdrawal request',
                ]);
            });
        } catch (\Throwable $exception) {
            if ($qrPath) {
                Storage::disk('public')->delete($qrPath);
            }

            throw $exception;
        }
        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully.',
            'data' => [
                'id' => $walletRequest->id,
                'status' => $walletRequest->status,
            ],
        ], 201);
    }

    /**
     * User wallet requests.
     */
    public function getMoneyRequest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'nullable', 'string', 'in:pending,processing,approved,rejected,failed'],
            'type' => ['sometimes', 'nullable', 'string', 'in:credit,debit'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $requests = WalletRequest::query()
            ->where('user_id', $request->user()->id)
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $validated['status'])
            )
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where('request_type', $validated['type'])
            )
            ->latest('id')
            ->paginate($validated['per_page'] ?? 15);
        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }
}
