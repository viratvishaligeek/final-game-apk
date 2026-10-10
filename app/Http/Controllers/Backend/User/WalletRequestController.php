<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\WalletRequest;
use App\Services\PushNotificationService;
use App\Services\WalletService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WalletRequestController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected ReferralService $referralService
    ) {}

    public function addRequests(Request $request)
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'nullable', 'string', 'in:pending,processing,approved,rejected,failed'],
        ]);

        $requests = WalletRequest::query()
            ->with('user:id,name,phone,balance')
            ->where('request_type', 'credit')
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $validated['status'])
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('backend.wallet.request-add', [
            'pageName' => 'Add Money Requests',
            'requests' => $requests,
        ]);
    }

    public function withdrawRequests(Request $request)
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'nullable', 'string', 'in:pending,processing,approved,rejected,failed'],
        ]);

        $requests = WalletRequest::query()
            ->with('user:id,name,phone,balance')
            ->where('request_type', 'debit')
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $validated['status'])
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('backend.wallet.request-withdraw', [
            'pageName' => 'Withdrawal Requests',
            'requests' => $requests,
        ]);
    }

    public function show(WalletRequest $walletRequest)
    {
        $walletRequest->load([
            'user:id,name,phone,balance',
            'processor:id,name',
        ]);

        return view('backend.wallet.request-show', [
            'pageName' => 'Wallet Request Details',
            'walletRequest' => $walletRequest,
        ]);
    }

    public function approve(
        Request $request,
        WalletRequest $walletRequest
    ) {
        $validated = $request->validate([
            'admin_remark' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use (
                $request,
                $walletRequest,
                $validated
            ) {
                $walletRequest = WalletRequest::query()
                    ->lockForUpdate()
                    ->findOrFail($walletRequest->id);

                if ($walletRequest->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'request' => 'This request has already been processed.',
                    ]);
                }

                $user = User::query()
                    ->lockForUpdate()
                    ->findOrFail($walletRequest->user_id);

                if ($walletRequest->request_type === 'credit') {
                    if ($walletRequest->payment_method !== 'manual_upi') {
                        throw ValidationException::withMessages([
                            'request' => 'Gateway payments can only be approved after server-side payment verification.',
                        ]);
                    }

                    $this->walletService->credit(
                        $user,
                        (float) $walletRequest->amount,
                        $walletRequest->remark ?: 'Wallet top-up approved'
                    );
                    $this->referralService->recordSuccessfulDeposit($user, $walletRequest);
                }

                if ($walletRequest->request_type === 'debit') {
                    if (
                        (float) $walletRequest->amount >
                        (float) $user->balance
                    ) {
                        throw ValidationException::withMessages([
                            'amount' => 'User no longer has sufficient wallet balance.',
                        ]);
                    }

                    $this->walletService->debit(
                        $user,
                        (float) $walletRequest->amount,
                        $walletRequest->remark ?: 'Wallet withdrawal approved'
                    );
                }

                $walletRequest->update([
                    'status' => 'approved',
                    'processed_by' => $request->user()->id,
                    'admin_remark' => $validated['admin_remark'] ?? null,
                    'processed_at' => now(),
                ]);
            });

            $walletRequest->refresh();
            $this->notifyWalletRequest($walletRequest, 'approved');

            return back()->with(
                'success',
                'Wallet request approved successfully.'
            );
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Unable to approve wallet request.'
            );
        }
    }

    public function reject(
        Request $request,
        WalletRequest $walletRequest
    ) {
        $validated = $request->validate([
            'admin_remark' => ['required', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use (
                $request,
                $walletRequest,
                $validated
            ) {
                $walletRequest = WalletRequest::query()
                    ->lockForUpdate()
                    ->findOrFail($walletRequest->id);

                if ($walletRequest->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'request' => 'This request has already been processed.',
                    ]);
                }

                $walletRequest->update([
                    'status' => 'rejected',
                    'processed_by' => $request->user()->id,
                    'admin_remark' => $validated['admin_remark'],
                    'processed_at' => now(),
                ]);
            });

            $walletRequest->refresh();
            $this->notifyWalletRequest($walletRequest, 'rejected');

            return back()->with(
                'success',
                'Wallet request rejected successfully.'
            );
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Unable to reject wallet request.'
            );
        }
    }

    /**
     * Persist a private notification and attempt delivery after the wallet
     * transaction has committed. A push-provider outage must not undo or
     * misreport a successful wallet operation.
     */
    private function notifyWalletRequest(WalletRequest $walletRequest, string $status): void
    {
        try {
            $user = User::query()->find($walletRequest->user_id);
            if (!$user) {
                return;
            }

            $isDeposit = $walletRequest->request_type === 'credit';
            $title = match ([$isDeposit, $status]) {
                [true, 'approved'] => 'Deposit approved',
                [false, 'approved'] => 'Withdrawal approved',
                [true, 'rejected'] => 'Deposit request rejected',
                default => 'Withdrawal request rejected',
            };
            $amount = number_format((float) $walletRequest->amount, 2);
            $body = match ($status) {
                'approved' => sprintf('Your %s of ₹%s was approved.', $isDeposit ? 'deposit' : 'withdrawal', $amount),
                default => sprintf('Your %s request of ₹%s was rejected.', $isDeposit ? 'deposit' : 'withdrawal', $amount),
            };

            Notification::query()->create([
                'user_id' => $user->id,
                'subject' => $title,
                'message' => $body,
            ]);

            $accepted = app(PushNotificationService::class)->sendToUser($user, $title, $body);
            Log::info('Private wallet notification processed.', [
                'wallet_request_id' => $walletRequest->id,
                'user_id' => $user->id,
                'status' => $status,
                'fcm_accepted_count' => $accepted,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Private wallet notification processing failed.', [
                'wallet_request_id' => $walletRequest->id,
                'user_id' => $walletRequest->user_id,
                'status' => $status,
                'error_class' => get_class($exception),
                'error' => $exception->getMessage(),
            ]);
        }
    }

}
