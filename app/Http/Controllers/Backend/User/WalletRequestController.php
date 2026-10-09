<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletRequest;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletRequestController extends Controller
{
    public function __construct(
        protected WalletService $walletService
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
}
