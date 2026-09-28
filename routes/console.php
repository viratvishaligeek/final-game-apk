<?php

use App\Models\Admin;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

Artisan::command('permissions:setup', function () {
    $allPermissions = [
        'game-browse',
        'game-read',
        'game-edit',
        'game-add',
        'game-delete',

        'page-browse',
        'page-read',
        'page-edit',
        'page-add',
        'page-delete',

        'member-browse',
        'member-read',
        'member-edit',
        'member-add',
        'member-delete',

        'role-browse',
        'role-read',
        'role-edit',
        'role-add',
        'role-delete',

        'user-browse',
        'user-read',
        'user-edit',
        'user-add',
        'user-delete',

        'faqs-browse',
        'faqs-read',
        'faqs-edit',
        'faqs-add',
        'faqs-delete',
    ];
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    DB::table('model_has_permissions')->delete();
    DB::table('model_has_roles')->delete();
    DB::table('role_has_permissions')->delete();

    Permission::query()->delete();
    Role::query()->delete();

    DB::statement('ALTER TABLE permissions AUTO_INCREMENT = 1');
    DB::statement('ALTER TABLE roles AUTO_INCREMENT = 1');

    foreach ($allPermissions as $permission) {
        Permission::create(['name' => $permission, 'guard_name' => 'admin']);
    }

    $superadminRole = Role::create(['name' => 'superadmin', 'guard_name' => 'admin']);
    $superadminRole->syncPermissions(Permission::all());

    $user = Admin::find(1);
    if ($user) {
        $user->assignRole('superadmin');
        $this->info('User assigned the superadmin role successfully.');
    } else {
        $this->error('Admin not found.');
    }
    $this->info('Roles and permissions have been set up successfully.');
})->describe('Setup roles and permissions for the application');
