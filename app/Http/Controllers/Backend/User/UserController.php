<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
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

    public function index()
    {
        $pageName = 'Users List';
        $users = User::latest()->get();
        return view('backend.users.index', compact('pageName', 'users'));
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
                    'Initial wallet balance assigned by admin'
                );
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'User added successfully.');
    }

    public function show(User $user)
    {
        $transactions = $user->transactions()->latest('id')->paginate(15, ['*'], 'transactions_page');

        $bids = $user->bids()->with('game:id,name')->latest('id')->paginate(15, ['*'], 'bids_page');

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

        return view('backend.users.show', [
            'pageName'     => 'User Details',
            'user'         => $user,
            'transactions' => $transactions,
            'bids'         => $bids,
            'walletStats'  => $walletStats,
            'gameStats'    => $gameStats,
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
            ->where('id', '!=', $user->id)
            ->exists();

        if ($duplicatePhone) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already registered.'],
            ]);
        }

        $targetBalance = round((float) $validated['balance'], 2);
        unset($validated['balance']);

        if (!empty($request->password)) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $userData = $this->mapPaymentFields($validated);

        DB::transaction(function () use ($id, $userData, $targetBalance) {
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($id);

            $lockedUser->update($userData);

            $currentBalance = round((float) $lockedUser->balance, 2);

            if ($targetBalance > $currentBalance) {
                $this->walletService->credit(
                    $lockedUser,
                    round($targetBalance - $currentBalance, 2),
                    'Wallet balance adjusted by admin through user edit'
                );
            } elseif ($targetBalance < $currentBalance) {
                $this->walletService->debit(
                    $lockedUser,
                    round($currentBalance - $targetBalance, 2),
                    'Wallet balance adjusted by admin through user edit'
                );
            }
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
