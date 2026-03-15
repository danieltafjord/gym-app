<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'team.view',
            'team.create',
            'team.update',
            'team.delete',
            'gym.view',
            'gym.create',
            'gym.update',
            'gym.delete',
            'membership-plan.view',
            'membership-plan.create',
            'membership-plan.update',
            'membership-plan.delete',
            'member.view',
            'member.create',
            'member.update',
            'member.delete',
            'membership.view-own',
            'membership.create-own',
            'membership.cancel-own',
            'team-member.invite',
            'team-member.remove',
            'platform.manage-teams',
            'platform.manage-users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'sanctum']);

        $teamOwner = Role::firstOrCreate(['name' => 'team-owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'team-owner', 'guard_name' => 'sanctum']);

        $teamAdmin = Role::firstOrCreate(['name' => 'team-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'team-admin', 'guard_name' => 'sanctum']);

        $member = Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'member', 'guard_name' => 'sanctum']);

        $teamOwner->syncPermissions([
            'team.view', 'team.update',
            'gym.view', 'gym.create', 'gym.update', 'gym.delete',
            'membership-plan.view', 'membership-plan.create', 'membership-plan.update', 'membership-plan.delete',
            'member.view', 'member.create', 'member.update', 'member.delete',
            'team-member.invite', 'team-member.remove',
        ]);

        $teamAdmin->syncPermissions([
            'team.view',
            'gym.view', 'gym.create', 'gym.update',
            'membership-plan.view',
            'member.view', 'member.create', 'member.update',
            'team-member.invite', 'team-member.remove',
        ]);

        $member->syncPermissions([
            'membership.view-own',
            'membership.create-own',
            'membership.cancel-own',
        ]);
    }
}
