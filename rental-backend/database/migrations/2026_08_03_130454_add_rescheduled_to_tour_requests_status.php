<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite stores enum as plain varchar — no action needed.
        // MySQL requires an explicit column modification to add the new value.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tour_requests MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled','rescheduled') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tour_requests MODIFY COLUMN status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
