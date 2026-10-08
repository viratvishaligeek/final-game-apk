<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::where('guard_name', 'admin')->withCount('permissions')->latest()->get();
        $pageName = 'Roles Management';
        return view('backend.roles.index', compact('roles', 'pageName'));
    }

    public function create(): View
    {
        $pageName = 'Create New Role';
        return view('backend.roles.create', compact('pageName'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'admin')],
        ]);

        Role::create([
            'name' => strtolower(trim($validated['name'])),
            'guard_name' => 'admin',
        ]);
        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully. You can now assign permissions.');
    }

    public function edit(Role $role): View
    {
        $pageName = 'Edit Role & Assign Permissions';
        $permissions = Permission::where('guard_name', 'admin')->get();
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? ucfirst($parts[0]) : 'General';
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('backend.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions', 'pageName'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)->where('guard_name', 'admin')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name' => strtolower(trim($validated['name'])),
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);
        return redirect()->route('admin.roles.index')->with('success', 'Role and permissions updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'Super Admin role cannot be deleted.');
        }
        $role->delete();
        return back()->with('success', 'Role deleted successfully.');
    }
}
