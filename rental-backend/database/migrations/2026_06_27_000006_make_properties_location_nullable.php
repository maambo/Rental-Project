<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // The `location` free-text column is superseded by province_id/district_id/town_id/street_address.
            // Make it nullable so new properties don't need to supply it.
            $table->string('location')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('location')->nullable(false)->change();
        });
    }
};
