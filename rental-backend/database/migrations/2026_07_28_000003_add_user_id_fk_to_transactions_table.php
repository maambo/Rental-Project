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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('UserID')->constrained('users')->nullOnDelete();

            // 'CARD' fits in 4 chars but 'MOBILE_MONEY' does not — widen ahead of the
            // Payments module, which writes both values into this column.
            $table->string('Type', 20)->nullable()->change();
        });

        // Backfill user_id from the legacy UserID string column wherever it's a plain
        // numeric id. Non-numeric UserID values are left alone (user_id stays null) so
        // they can be reviewed manually rather than silently coerced to a wrong user.
        DB::table('transactions')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                if ($row->UserID !== null && ctype_digit((string) $row->UserID)) {
                    DB::table('transactions')->where('id', $row->id)->update([
                        'user_id' => (int) $row->UserID,
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->string('Type', 4)->nullable()->change();
        });
    }
};
