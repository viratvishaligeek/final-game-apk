<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletRequest;
use App\Services\AppSettingsService;
use App\Services\ReferralService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected AppSettingsService $settings,
        protected ReferralService $referralService
    ) {}

    private function gatewayValue(string $option, string $configKey): ?string
    {
        $value = $this->settings->value($option);

        if (!is_string($value) || trim($value) === '') {
            $value = config($configKey);
        }

        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : null;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $reservedWithdrawals = WalletRequest::query()
            ->where('user_id', $user->id)
            ->where('request_type', 'debit')
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        $availableBalance = round(
            max(0, (float) $user->balance - (float) $reservedWithdrawals),
            2
        );
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
                'available_balance' => $availableBalance,
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
    public function paymentMethods(): JsonResponse
    {
        $barcode = $this->settings->value('payment_bar_code');
        $manualQrUrl = $barcode
            ? asset('uploads/payment/' . ltrim((string) $barcode, '/'))
            : config('services.manual_upi.qr_url');

        $gatewayEnabled = $this->gatewayIsConfigured();

        return response()->json([
            'success' => true,
            'data' => [
                'manual_upi' => [
                    'upi_id' => config('services.manual_upi.upi_id'),
                    'name' => config('services.manual_upi.name'),
                    'qr_url' => $manualQrUrl,
                ],
                'gateway' => [
                    'enabled' => $gatewayEnabled,
                ],
                'limits' => [
                    'min_deposit' => $this->settings->get('min_deposit'),
                    'max_deposit' => $this->settings->get('max_deposit'),
                    'min_withdraw' => $this->settings->get('min_withdraw'),
                    'max_withdraw' => $this->settings->get('max_withdraw'),
                ],
                'notices' => [
                    'add_money' => $this->settings->value('add_money_notice'),
                    'withdraw' => $this->settings->value('withdraw_money_notice'),
                ],
            ],
        ]);
    }

    private function gatewayIsConfigured(): bool
    {
        return $this->gatewayValue('api_key', 'services.upi_gateway.key') !== null
            && $this->gatewayValue('create_order_url', 'services.upi_gateway.create_order_url') !== null
            && $this->gatewayValue('status_url', 'services.upi_gateway.status_url') !== null
            && $this->gatewayValue('return_url', 'services.upi_gateway.return_url') !== null;
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
        } catch (Throwable $exception) {
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

        $gatewayKey = $this->gatewayValue('api_key', 'services.upi_gateway.key');
        $createOrderUrl = $this->gatewayValue('create_order_url', 'services.upi_gateway.create_order_url');
        $statusUrl = $this->gatewayValue('status_url', 'services.upi_gateway.status_url');
        $returnUrl = $this->gatewayValue('return_url', 'services.upi_gateway.return_url');
        $webhookUrl = $this->gatewayValue('webhook_url', 'services.upi_gateway.webhook_url');

        if (!$gatewayKey || !$createOrderUrl || !$statusUrl || !$returnUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway is not fully configured. Please contact support.',
            ], 503);
        }

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
                    $createOrderUrl,
                    [
                        'key' => $gatewayKey,
                        'client_txn_id' => $clientTxnId,
                        'amount' => $validated['amount'],
                        'p_info' => 'Wallet Top-up',
                        'customer_name' => $user->name,
                        'customer_email' => $user->email ?? 'noemail@gmail.com',
                        'customer_mobile' => $user->phone,
                        'redirect_url' => $returnUrl,
                        'webhook_url' => $webhookUrl,
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
            $paymentUrl = $result['data']['payment_url'] ?? null;
            if (!is_string($paymentUrl) || !filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
                $walletRequest->update([
                    'status' => 'failed',
                    'gateway_status' => 'invalid_payment_url',
                    'remark' => 'Gateway returned an invalid payment URL.',
                ]);

                Log::warning('UPI gateway returned no valid payment URL.', [
                    'wallet_request_id' => $walletRequest->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway returned an invalid payment link. Please try again.',
                ], 502);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment order created.',
                'data' => [
                    'request_id' => $walletRequest->id,
                    'client_txn_id' => $clientTxnId,
                    'payment_url' => $paymentUrl,
                ],
            ]);
        } catch (Throwable $e) {
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

    public function gatewayReturn(Request $request): Response
    {
        $clientTxnId = $request->query('client_txn_id');

        if (!$clientTxnId) {
            return $this->gatewayReturnResponse(
                $request,
                false,
                'Invalid transaction.',
                ['status' => 'failed'],
                422
            );
        }

        $walletRequest = WalletRequest::query()
            ->where('client_txn_id', $clientTxnId)
            ->first();

        if (!$walletRequest) {
            return $this->gatewayReturnResponse(
                $request,
                false,
                'Transaction not found.',
                ['status' => 'failed'],
                404
            );
        }

        try {
            $this->verifyGatewayTransaction($walletRequest);
        } catch (Throwable $exception) {
            report($exception);
        }

        $walletRequest->refresh();

        return $this->gatewayReturnResponse(
            $request,
            $walletRequest->status === 'approved',
            match ($walletRequest->status) {
                'approved' => 'Payment successful and wallet credited.',
                'failed' => 'Payment failed.',
                default => 'Payment is being verified.',
            },
            [
                'status' => $walletRequest->status,
                'request_id' => $walletRequest->id,
            ]
        );
    }

    /**
     * API clients keep the JSON contract. Browser-based gateway returns go
     * back to the wallet screen instead of leaving users on a raw JSON page.
     */
    private function gatewayReturnResponse(
        Request $request,
        bool $success,
        string $message,
        array $data = [],
        int $httpStatus = 200
    ): Response {
        $payload = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];

        if ($request->expectsJson()) {
            return response()->json($payload, $httpStatus);
        }

        $frontendUrl = $this->gatewayValue(
            'frontend_return_url',
            'services.upi_gateway.frontend_return_url'
        ) ?? rtrim((string) config('app.url'), '/') . '/wallet/add';

        $query = http_build_query([
            'gateway_return' => '1',
            'status' => $data['status'] ?? 'failed',
            'request_id' => $data['request_id'] ?? null,
        ]);

        $separator = str_contains($frontendUrl, '?') ? '&' : '?';

        return redirect()->away($frontendUrl . $separator . $query);
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
        } catch (Throwable $e) {
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
        $gatewayKey = $this->gatewayValue('api_key', 'services.upi_gateway.key');
        if (!$gatewayKey) {
            return false;
        }

        $statusUrl = $this->gatewayValue('status_url', 'services.upi_gateway.status_url');
        if (!$statusUrl) {
            return false;
        }

        $response = Http::timeout(30)
            ->acceptJson()
            ->post(
                $statusUrl,
                [
                    'key' => $gatewayKey,
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
                $this->referralService->recordSuccessfulDeposit($user, $requestRow);

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
        } catch (Throwable $exception) {
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
            'statuses' => ['sometimes', 'array'],
            'statuses.*' => ['string', 'in:pending,processing,approved,rejected,failed'],
            'type' => ['sometimes', 'nullable', 'string', 'in:credit,debit'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $statuses = $validated['statuses'] ?? [];

        $requests = WalletRequest::query()
            ->where('user_id', $request->user()->id)
            ->when(
                $statuses !== [],
                fn ($query) => $query->whereIn('status', $statuses),
                fn ($query) => $request->filled('status')
                    ? $query->where('status', $validated['status'])
                    : $query
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
