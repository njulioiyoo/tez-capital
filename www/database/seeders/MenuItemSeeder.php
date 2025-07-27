<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing menu items
        MenuItem::query()->delete();

        // Default menu items with proper structure
        $menuItems = [
            // Dashboard
            [
                'title' => 'Dashboard',
                'href' => '/dashboard',
                'icon' => 'LayoutGrid',
                'position' => 1,
                'parent_id' => null,
                'is_active' => true,
            ],

            // Reports parent
            [
                'title' => 'Report',
                'href' => null,
                'icon' => 'BarChart3',
                'position' => 2,
                'parent_id' => null,
                'is_active' => true,
            ],

            // Analytics under Reports
            [
                'title' => 'Analytics',
                'href' => '/reports/analytics',
                'icon' => null,
                'position' => 1,
                'parent_id' => null, // Will be updated after Report is created
                'is_active' => true,
            ],

            // Financial Reports under Reports
            [
                'title' => 'Financial Reports',
                'href' => '/reports/financial',
                'icon' => null,
                'position' => 2,
                'parent_id' => null, // Will be updated after Report is created
                'is_active' => true,
            ],

            // Separator
            [
                'title' => '',
                'href' => null,
                'icon' => null,
                'position' => 3,
                'parent_id' => null,
                'is_separator' => true,
                'is_active' => true,
            ],

            // System parent
            [
                'title' => 'System',
                'href' => null,
                'icon' => 'Settings',
                'position' => 4,
                'parent_id' => null,
                'is_active' => true,
            ],

            // User under System
            [
                'title' => 'Users',
                'href' => '/system/users',
                'icon' => 'Users',
                'position' => 1,
                'parent_id' => null, // Will be updated after System is created
                'is_active' => true,
            ],

            // Roles & Permissions under System
            [
                'title' => 'Roles & Permissions',
                'href' => '/system/roles',
                'icon' => 'Shield',
                'position' => 2,
                'parent_id' => null, // Will be updated after System is created
                'is_active' => true,
            ],

            // Menu under System
            [
                'title' => 'Menu',
                'href' => '/system/menu',
                'icon' => 'FileText',
                'position' => 3,
                'parent_id' => null, // Will be updated after System is created
                'is_active' => true,
            ],

            // Audit Log under System
            [
                'title' => 'Audit Log',
                'href' => '/system/audit-log',
                'icon' => 'Activity',
                'position' => 4,
                'parent_id' => null, // Will be updated after System is created
                'is_active' => true,
            ],

            // Configuration under System
            [
                'title' => 'Configuration',
                'href' => '/system/configurations',
                'icon' => 'Settings',
                'position' => 5,
                'parent_id' => null, // Will be updated after System is created
                'is_active' => true,
            ],

            // Separator
            [
                'title' => '',
                'href' => null,
                'icon' => null,
                'position' => 5,
                'parent_id' => null,
                'is_separator' => true,
                'is_active' => true,
            ],

            // Content parent
            [
                'title' => 'Content',
                'href' => null,
                'icon' => 'FileText',
                'position' => 6,
                'parent_id' => null,
                'is_active' => true,
            ],

            // Education under Content
            [
                'title' => 'Education',
                'href' => '/education',
                'icon' => 'GraduationCap',
                'position' => 1,
                'parent_id' => null, // Will be updated after Content is created
                'is_active' => true,
            ],
        ];

        // Create menu items and track IDs for parent relationships
        $createdItems = [];

        // First pass: create all items
        foreach ($menuItems as $item) {
            $createdItem = MenuItem::create($item);
            $createdItems[$item['title']] = $createdItem;
        }

        // Second pass: update parent relationships
        // Update Report children
        if (isset($createdItems['Report'])) {
            if (isset($createdItems['Analytics'])) {
                $createdItems['Analytics']->update(['parent_id' => $createdItems['Report']->id]);
            }
            if (isset($createdItems['Financial Reports'])) {
                $createdItems['Financial Reports']->update(['parent_id' => $createdItems['Report']->id]);
            }
        }

        // Update System children
        if (isset($createdItems['System'])) {
            if (isset($createdItems['Users'])) {
                $createdItems['Users']->update(['parent_id' => $createdItems['System']->id]);
            }
            if (isset($createdItems['Roles & Permissions'])) {
                $createdItems['Roles & Permissions']->update(['parent_id' => $createdItems['System']->id]);
            }
            if (isset($createdItems['Menu'])) {
                $createdItems['Menu']->update(['parent_id' => $createdItems['System']->id]);
            }
            if (isset($createdItems['Audit Log'])) {
                $createdItems['Audit Log']->update(['parent_id' => $createdItems['System']->id]);
            }
            if (isset($createdItems['Configuration'])) {
                $createdItems['Configuration']->update(['parent_id' => $createdItems['System']->id]);
            }
        }

        // Update Content children
        if (isset($createdItems['Content'])) {
            if (isset($createdItems['Education'])) {
                $createdItems['Education']->update(['parent_id' => $createdItems['Content']->id]);
            }
        }
    }
}
