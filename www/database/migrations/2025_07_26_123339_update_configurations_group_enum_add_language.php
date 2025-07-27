<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to modify the check constraint
        DB::statement('ALTER TABLE configurations DROP CONSTRAINT IF EXISTS configurations_group_check');
        DB::statement("ALTER TABLE configurations ADD CONSTRAINT configurations_group_check CHECK (\"group\" IN ('general', 'branding', 'homepage', 'credit', 'maintenance', 'contact', 'language'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original constraint
        DB::statement('ALTER TABLE configurations DROP CONSTRAINT IF EXISTS configurations_group_check');
        DB::statement("ALTER TABLE configurations ADD CONSTRAINT configurations_group_check CHECK (\"group\" IN ('general', 'branding', 'homepage', 'credit', 'maintenance', 'contact'))");
    }
};
