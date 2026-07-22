<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_tiers', function (Blueprint $table) {
            $table->enum('tier_type', ['landlord', 'tenant', 'worker'])
                  ->default('landlord')
                  ->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('verification_tiers', function (Blueprint $table) {
            $table->dropColumn('tier_type');
        });
    }
};
