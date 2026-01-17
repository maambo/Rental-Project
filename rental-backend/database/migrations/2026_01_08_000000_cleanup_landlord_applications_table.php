<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('landlord_applications', function (Blueprint $table) {
            // Drop the old tier column if it exists
            if (Schema::hasColumn('landlord_applications', 'tier')) {
                $table->dropColumn('tier');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landlord_applications', function (Blueprint $table) {
            $table->string('tier')->default('small'); // Restore with default if rolled back
        });
    }
};
