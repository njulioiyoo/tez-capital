<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the href for Roles & Permissions menu item
        DB::table('menu_items')
            ->where('title', 'Roles & Permissions')
            ->where('href', '/users/roles')
            ->update(['href' => '/roles']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the href back to original value
        DB::table('menu_items')
            ->where('title', 'Roles & Permissions')
            ->where('href', '/roles')
            ->update(['href' => '/users/roles']);
    }
};