<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
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
        $validated = $request->validate([
            'name'       => 'required|string|max:200',
            'phone'      => 'required|string|max:20|unique:users,phone',
            'password'   => 'required|string|min:6',
            'gender'     => 'nullable|string|in:Male,Female,Other',
            'city'       => 'nullable|string|max:200',
            'address'    => 'nullable|string|max:255',
            'balance'    => 'required|integer|min:0',
            'bank'       => 'nullable|string|max:100',
            'acc'        => 'nullable|string|max:100',
            'ifsc'       => 'nullable|string|max:100',
            'holdername' => 'nullable|string|max:100',
            'phonepe'    => 'nullable|string|max:100',
            'gpay'       => 'nullable|string|max:20',
            'paytm'      => 'nullable|string|max:20',
            'status'     => 'required|in:active,inactive',
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User added successfully.');
    }

    public function show(string $id)
    {
        $pageName = 'User Details';
        $user = User::findOrFail($id);
        return view('backend.users.show', compact('user', 'pageName'));
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

        $validated = $request->validate([
            'name'       => 'required|string|max:200',
            'phone'      => 'required|string|max:20|unique:users,phone,' . $user->id,
            'password'   => 'nullable|string|min:6',
            'gender'     => 'nullable|string|in:Male,Female,Other',
            'city'       => 'nullable|string|max:200',
            'address'    => 'nullable|string|max:255',
            'balance'    => 'required|integer|min:0',
            'bank'       => 'nullable|string|max:100',
            'acc'        => 'nullable|string|max:100',
            'ifsc'       => 'nullable|string|max:100',
            'holdername' => 'nullable|string|max:100',
            'phonepe'    => 'nullable|string|max:100',
            'gpay'       => 'nullable|string|max:20',
            'paytm'      => 'nullable|string|max:20',
            'status'     => 'required|in:active,inactive',
        ]);

        if (!empty($request->password)) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function toggleStatus(string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->status = ($user->status === 'active') ? 'inactive' : 'active';
            $user->save();

            $msg = $user->status === 'active' ? 'User Unblocked / Activated successfully.' : 'User Blocked / Deactivated successfully.';
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Action failed: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
