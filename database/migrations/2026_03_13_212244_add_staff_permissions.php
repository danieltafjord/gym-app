<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = ['team-member.invite', 'team-member.remove'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        $teamOwner = Role::where('name', 'team-owner')->where('guard_name', 'web')->first();
        $teamOwner?->givePermissionTo($permissions);

        $teamAdmin = Role::where('name', 'team-admin')->where('guard_name', 'web')->first();
        $teamAdmin?->givePermissionTo($permissions);
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', ['team-member.invite', 'team-member.remove'])->delete();
    }
};
