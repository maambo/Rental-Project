<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * StorePropertyRequest/UpdatePropertyRequest validate `bedrooms` and `bathrooms`
 * as `nullable`, but the original create_properties_table migration declared them
 * as plain NOT NULL integers. Submitting a listing without them — which is normal
 * for commercial subtypes (shop, office_space, warehouse, plot, farm) — raised
 * "NOT NULL constraint failed: properties.bedrooms" and returned a 500.
 *
 * The frontend already renders these defensively (`bedrooms != null && > 0`), so
 * nullable columns match the intended contract.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->integer('bedrooms')->nullable()->change();
            $table->integer('bathrooms')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Backfill so the NOT NULL constraint can be re-applied.
        DB::table('properties')->whereNull('bedrooms')->update(['bedrooms' => 0]);
        DB::table('properties')->whereNull('bathrooms')->update(['bathrooms' => 0]);

        Schema::table('properties', function (Blueprint $table) {
            $table->integer('bedrooms')->nullable(false)->change();
            $table->integer('bathrooms')->nullable(false)->change();
        });
    }
};
