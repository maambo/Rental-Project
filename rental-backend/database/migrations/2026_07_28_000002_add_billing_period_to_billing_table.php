<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('billing', function (Blueprint $table) {
            $table->date('billing_period')->nullable()->after('Year');
        });

        // Backfill billing_period from the existing Date column (first day of that month).
        // Done in PHP rather than a raw DATE_FORMAT/strftime call so it works identically
        // on SQLite (dev/test) and MySQL (production).
        DB::table('billing')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $date = $row->Date ? \Illuminate\Support\Carbon::parse($row->Date) : now();

                DB::table('billing')->where('id', $row->id)->update([
                    'billing_period' => $date->startOfMonth()->toDateString(),
                ]);
            }
        });

        // One bill per lease per billing period — replaces the old (UserID, Year) constraint
        // and is what actually allows recurring monthly rent billing. Bills not tied to a
        // lease (lease_agreement_id null) are excluded from the uniqueness check.
        Schema::table('billing', function (Blueprint $table) {
            $table->unique(['lease_agreement_id', 'billing_period'], 'billing_unique_lease_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing', function (Blueprint $table) {
            $table->dropUnique('billing_unique_lease_period');
            $table->dropColumn('billing_period');
        });
    }
};
