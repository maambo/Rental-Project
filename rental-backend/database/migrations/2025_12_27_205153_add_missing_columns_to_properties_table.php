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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('province', 100)->nullable()->after('location');
            $table->string('town', 100)->nullable()->after('province');
            $table->integer('square_feet')->nullable()->after('sqft');
            $table->json('amenities')->nullable()->after('square_feet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['province', 'town', 'square_feet', 'amenities']);
        });
    }
};
