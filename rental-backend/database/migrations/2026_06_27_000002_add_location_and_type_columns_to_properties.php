<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Drop old free-text location columns
            $table->dropColumn(['province', 'town', 'is_approved', 'sqft']);

            // Structured location (nullable during migration; will enforce NOT NULL after seeding FKs)
            $table->foreignId('province_id')->nullable()->after('location')->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('province_id')->constrained()->nullOnDelete();
            $table->foreignId('town_id')->nullable()->after('district_id')->constrained()->nullOnDelete();
            $table->string('street_address')->nullable()->after('town_id');

            // Make coordinates required
            $table->decimal('latitude', 10, 8)->nullable()->change();
            $table->decimal('longitude', 11, 8)->nullable()->change();

            // Property categorisation
            $table->enum('property_type', ['residential', 'commercial'])->nullable()->after('square_feet');
            $table->enum('property_subtype', ['house', 'apartment', 'room', 'farm', 'plot', 'shop', 'office_space', 'warehouse'])->nullable()->after('property_type');
            $table->enum('listing_type', ['rent', 'sale'])->nullable()->after('property_subtype');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('province_id');
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('town_id');
            $table->dropColumn(['street_address', 'property_type', 'property_subtype', 'listing_type']);
            $table->string('province', 100)->nullable();
            $table->string('town', 100)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->integer('sqft')->nullable();
        });
    }
};
