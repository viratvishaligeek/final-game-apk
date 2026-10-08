<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class MemberController extends Controller
{
    public function index(): View
    {
        $members = Admin::query()->with('roles')->latest()->get();
        $pageName = 'Members List';
        return view('backend.member.index', compact('members', 'pageName'));
    }

    public function create(): View
    {
        $roles = Role::query()->where('guard_name', 'admin')->orderBy('name')->get();
        return view('backend.member.create', [
            'pageName' => 'Create Member',
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'admin')],
        ]);

        DB::transaction(function () use ($validated) {
            $member = Admin::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'status' => $validated['status'],
            ]);
            $member->assignRole($validated['role']);
        });
        return redirect()
            ->route('admin.member.index')
            ->with('success', 'Member created successfully.');
    }

    public function edit(Admin $member): View
    {
        $roles = Role::query()->where('guard_name', 'admin')->orderBy('name')->get();
        $memberRole = $member->roles()->where('guard_name', 'admin')->value('name');
        return view('backend.member.edit', [
            'pageName' => 'Edit Member',
            'member' => $member,
            'roles' => $roles,
            'memberRole' => $memberRole,
        ]);
    }

    public function update(Request $request, Admin $member): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($member->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'admin')],
        ]);

        DB::transaction(function () use ($validated, $member) {
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
            ];
            if (!empty($validated['password'])) {
                $data['password'] = bcrypt($validated['password']);
            }
            $member->update($data);
            $member->syncRoles([$validated['role']]);
        });

        return redirect()->route('admin.member.index')->with('success', 'Member updated successfully.');
    }

    public function destroy(Admin $member): RedirectResponse
    {
        if (auth('admin')->id() === $member->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $member->delete();
        return back()->with('success', 'Member deleted successfully.');
    }
}
