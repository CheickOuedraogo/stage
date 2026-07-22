<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TYPE type_notification ADD VALUE IF NOT EXISTS 'projet_mis_en_cours'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // Cannot remove values from enums in PostgreSQL without recreating the type.
        // The down method intentionally left blank.
    }
};
