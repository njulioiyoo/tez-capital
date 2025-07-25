<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DefaultRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Create default permissions
        $permissionData = [
            // User Management
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'View user list and details', 'group' => 'User Management'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Create new users', 'group' => 'User Management'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'description' => 'Edit user information', 'group' => 'User Management'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Delete users', 'group' => 'User Management'],
            ['name' => 'users.bulk_actions', 'display_name' => 'User Bulk Actions', 'description' => 'Perform bulk actions on users', 'group' => 'User Management'],
            
            // Role & Permission Management
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'description' => 'View roles and permissions', 'group' => 'Role Management'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'description' => 'Create new roles', 'group' => 'Role Management'],
            ['name' => 'roles.edit', 'display_name' => 'Edit Roles', 'description' => 'Edit role information', 'group' => 'Role Management'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Delete roles', 'group' => 'Role Management'],
            ['name' => 'permissions.view', 'display_name' => 'View Permissions', 'description' => 'View permissions', 'group' => 'Role Management'],
            ['name' => 'permissions.create', 'display_name' => 'Create Permissions', 'description' => 'Create new permissions', 'group' => 'Role Management'],
            ['name' => 'permissions.edit', 'display_name' => 'Edit Permissions', 'description' => 'Edit permission information', 'group' => 'Role Management'],
            ['name' => 'permissions.delete', 'display_name' => 'Delete Permissions', 'description' => 'Delete permissions', 'group' => 'Role Management'],
            
            // System Configuration
            ['name' => 'menu.view', 'display_name' => 'View Menu', 'description' => 'View menu configuration', 'group' => 'System Configuration'],
            ['name' => 'menu.create', 'display_name' => 'Create Menu', 'description' => 'Create menu items', 'group' => 'System Configuration'],
            ['name' => 'menu.edit', 'display_name' => 'Edit Menu', 'description' => 'Edit menu items', 'group' => 'System Configuration'],
            ['name' => 'menu.delete', 'display_name' => 'Delete Menu', 'description' => 'Delete menu items', 'group' => 'System Configuration'],
            ['name' => 'menu.reorder', 'display_name' => 'Reorder Menu', 'description' => 'Reorder menu items', 'group' => 'System Configuration'],
            
            // Audit Logs
            ['name' => 'audit-logs.view', 'display_name' => 'View Audit Logs', 'description' => 'View audit logs', 'group' => 'Audit & Monitoring'],
            ['name' => 'audit-logs.export', 'display_name' => 'Export Audit Logs', 'description' => 'Export audit logs', 'group' => 'Audit & Monitoring'],
            
            // Dashboard
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'description' => 'Access dashboard', 'group' => 'General'],
            
            // Reports
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'description' => 'View reports section', 'group' => 'Reports'],
            ['name' => 'reports.analytics', 'display_name' => 'Analytics Reports', 'description' => 'View analytics reports', 'group' => 'Reports'],
            ['name' => 'reports.financial', 'display_name' => 'Financial Reports', 'description' => 'View financial reports', 'group' => 'Reports'],
        ];

        foreach ($permissionData as $permData) {
            Permission::firstOrCreate(['name' => $permData['name']], $permData);
        }

        // Create default roles
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'display_name' => 'Super Administrator',
            'description' => 'Full system access with all permissions'
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'display_name' => 'Administrator', 
            'description' => 'System administrator with most permissions'
        ]);

        $managerRole = Role::firstOrCreate([
            'name' => 'Manager',
            'display_name' => 'Manager',
            'description' => 'Manager with limited administrative access'
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'User',
            'display_name' => 'User',
            'description' => 'Standard user with basic access'
        ]);

        // Assign permissions to roles
        $superAdminRole->syncPermissions(Permission::all());

        $adminRole->syncPermissions([
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit',
            'permissions.view',
            'menu.view', 'menu.create', 'menu.edit', 'menu.delete', 'menu.reorder',
            'audit-logs.view', 'audit-logs.export',
            'dashboard.view',
            'reports.view', 'reports.analytics',
        ]);

        $managerRole->syncPermissions([
            'users.view', 'users.create', 'users.edit',
            'roles.view',
            'permissions.view',
            'dashboard.view',
            'reports.view',
        ]);

        $userRole->syncPermissions([
            'dashboard.view',
        ]);

        $this->command->info('Default roles and permissions created successfully!');
    }
}