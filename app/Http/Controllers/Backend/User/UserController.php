<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\WalletService;

class UserController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'live_status' => ['nullable', 'in:active,inactive'],
        ]);

        $pageName = 'Users List';
        $liveStatus = $validated['live_status'] ?? null;
        $users = User::query()
            ->when($liveStatus, fn ($query) => $query->where('live_status', $liveStatus))
            ->latest()
            ->get();

        return view('backend.users.index', compact('pageName', 'users', 'liveStatus'));
    }

    public function create()
    {
        $pageName = 'Add New User';
        return view('backend.users.create', compact('pageName'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => preg_replace('/[\s-]/', '', trim((string) $request->input('phone', ''))) ?? '',
        ]);

        $validated = $request->validate([
            'name'       => 'required|string|max:200',
            'phone'      => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/', 'unique:users,phone'],
            'password'   => 'required|string|min:6',
            'gender'     => 'nullable|string|in:Male,Female,Other',
            'city'       => 'nullable|string|max:200',
            'address'    => 'nullable|string|max:255',
            'balance' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:1000000'],
            'bank'       => 'nullable|string|max:100',
            'acc'        => 'nullable|string|max:100',
            'ifsc'       => 'nullable|string|max:20',
            'holdername' => 'nullable|string|max:100',
            'phonepe'    => 'nullable|string|max:100',
            'gpay'       => 'nullable|string|max:20',
            'paytm'      => 'nullable|string|max:20',
            'status'     => 'required|in:active,inactive',
        ]);

        $duplicatePhone = User::query()
            ->whereRaw("REPLACE(REPLACE(phone, ' ', ''), '-', '') = ?", [$validated['phone']])
            ->exists();

        if ($duplicatePhone) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already registered.'],
            ]);
        }

        $initialBalance = round((float) $validated['balance'], 2);
        unset($validated['balance']);
        $userData = $this->mapPaymentFields($validated);
        $userData['balance'] = 0;

        DB::transaction(function () use ($userData, $initialBalance) {
            $user = User::create($userData);

            if ($initialBalance > 0) {
                $this->walletService->credit(
                    $user,
                    $initialBalance,
                    'Initial wallet balance assigned by admin',
                    ['admin_id' => auth('admin')->id()]
                );
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'User added successfully.');
    }

    public function show(User $user)
    {
        $transactions = $user->transactions()->latest('id')->paginate(15, ['*'], 'transactions_page');

        // A bid submission is identified by order_no. Do not collapse separate
        // submissions merely because their game, bet type or timestamp match.
        $submissionStats = $user->bids()
            ->selectRaw('order_no, COUNT(*) as total_bids, COALESCE(SUM(amount), 0) as total_amount, MIN(id) as representative_bid_id, MAX(id) as latest_bid_id, MIN(created_at) as submitted_at')
            ->groupBy('order_no')
            ->orderByDesc('latest_bid_id')
            ->get();

        $representativeBids = Bid::query()
            ->with('game:id,name')
            ->whereIn('id', $submissionStats->pluck('representative_bid_id'))
            ->get()
            ->keyBy('id');

        $gameBidStats = $submissionStats->map(function ($submission) use ($representativeBids) {
            $representative = $representativeBids->get($submission->representative_bid_id);

            return (object) [
                'order_no' => $submission->order_no,
                'total_bids' => (int) $submission->total_bids,
                'total_amount' => (float) $submission->total_amount,
                'latest_bid_id' => (int) $submission->latest_bid_id,
                'submitted_at' => $submission->submitted_at,
                'game_id' => $representative?->game_id,
                'game_name' => $representative?->game?->name ?? 'Game Deleted',
                'type' => $representative?->type ?? 'unknown',
                'game_date' => $representative?->game_date,
            ];
        });

        $walletStats = $user->completedTransactions()
            ->selectRaw("
            COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as credit,
            COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) as debit
        ")->first();

        $gameStats = $user->bids()
            ->selectRaw("
            COUNT(*) as total_bids,
            COALESCE(SUM(CAST(amount AS DECIMAL(15,2))), 0) as total_amount
        ")->first();

        $bidTypeStats = $user->bids()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'jodi' THEN 1 ELSE 0 END), 0) as jodi_count,
                COALESCE(SUM(CASE WHEN type = 'haruf' THEN 1 ELSE 0 END), 0) as haruf_count,
                COALESCE(SUM(CASE WHEN type = 'cross' THEN 1 ELSE 0 END), 0) as crossing_count,
                COALESCE(SUM(CASE WHEN type = 'jodi' THEN amount ELSE 0 END), 0) as jodi_amount,
                COALESCE(SUM(CASE WHEN type = 'haruf' THEN amount ELSE 0 END), 0) as haruf_amount,
                COALESCE(SUM(CASE WHEN type = 'cross' THEN amount ELSE 0 END), 0) as crossing_amount
            ")->first();

        return view('backend.users.show', [
            'pageName'     => 'User Details',
            'user'         => $user,
            'transactions' => $transactions,
            'gameBidStats' => $gameBidStats,
            'walletStats'  => $walletStats,
            'gameStats'    => $gameStats,
            'bidTypeStats' => $bidTypeStats,
        ]);
    }


    public function bids(Request $request, User $user)
    {
        $validated = $request->validate([
            'order_no' => ['required', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        // Scope by the route-bound user first; an order number from another
        // account must never disclose its bids.
        $bids = $user->bids()
            ->with('game:id,name')
            ->where('order_no', $validated['order_no'])
            ->orderBy('id')
            ->paginate($validated['per_page'] ?? 100)
            ->withQueryString();

        return response()->json([
            'data' => $bids->getCollection()->map(fn ($bid) => [
                'id' => $bid->id,
                'order_no' => $bid->order_no,
                'game_name' => $bid->game?->name ?? 'Game Deleted',
                'game_date' => $bid->game_date ? \Illuminate\Support\Carbon::parse($bid->game_date)->format('d M Y') : '-',
                'type' => $bid->type,
                'number' => $bid->number,
                'amount' => round((float) $bid->amount, 2),
                'status' => $bid->status,
                'winning_amount' => round((float) $bid->winning_amount, 2),
                'created_at' => $bid->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A'),
            ])->values(),
            'pagination' => [
                'current_page' => $bids->currentPage(),
                'last_page' => $bids->lastPage(),
                'total' => $bids->total(),
                'total_amount' => round((float) $user->bids()->where('order_no', $validated['order_no'])->sum('amount'), 2),
            ],
        ]);
    }

    public function edit(string $id)
    {
        $pageName = 'Edit User';
        $user = User::findOrFail($id);
        return view('backend.users.edit', compact('user', 'pageName'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->merge([
            'phone' => preg_replace('/[\s-]/', '', trim((string) $request->input('phone', ''))) ?? '',
        ]);

        $validated = $request->validate([
            'name'       => 'required|string|max:200',
            'phone'      => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/', 'unique:users,phone,' . $user->id],
            'password'   => 'nullable|string|min:6',
            'gender'     => 'nullable|string|in:Male,Female,Other',
            'city'       => 'nullable|string|max:200',
            'address'    => 'nullable|string|max:255',
            'bank'       => 'nullable|string|max:100',
            'acc'        => 'nullable|string|max:100',
            'ifsc'       => 'nullable|string|max:20',
            'holdername' => 'nullable|string|max:100',
            'phonepe'    => 'nullable|string|max:100',
            'gpay'       => 'nullable|string|max:20',
            'paytm'      => 'nullable|string|max:20',
            'status'     => 'required|in:active,inactive',
        ]);

        $duplicatePhone = User::query()
            ->whereRaw("REPLACE(REPLACE(phone, ' ', ''), '-', '') = ?", [$validated['phone']])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($duplicatePhone) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already registered.'],
            ]);
        }

        if (!empty($request->password)) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $userData = $this->mapPaymentFields($validated);

        DB::transaction(function () use ($id, $userData) {
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($id);

            $lockedUser->update($userData);
        });

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function toggleStatus(string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->status = ($user->status === 'active') ? 'inactive' : 'active';
            $user->save();

            if ($user->status !== 'active') {
                $user->tokens()->delete();
            }

            $msg = $user->status === 'active' ? 'User Unblocked / Activated successfully.' : 'User Blocked / Deactivated successfully.';
            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'Unable to update user status. Please try again.');
        }
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->tokens()->delete();
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    /**
     * The admin form uses legacy field names; map them to actual user columns.
     */
    private function mapPaymentFields(array $data): array
    {
        foreach ([
            'bank' => 'bank_name',
            'acc' => 'account_number',
            'ifsc' => 'ifsc_code',
            'holdername' => 'account_holder_name',
        ] as $input => $column) {
            if (array_key_exists($input, $data)) {
                $data[$column] = $data[$input];
                unset($data[$input]);
            }
        }

        return $data;
    }
}
