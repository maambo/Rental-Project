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
        Schema::create('verification_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // basic, trusted, premium
            $table->string('display_name');
            $table->string('price_display'); // "Free", "Verified", "K300/mo"
            $table->integer('price_amount')->default(0); // For calculation
            $table->integer('property_limit')->default(5); // -1 for unlimited
            $table->json('features')->nullable();
            $table->json('styling')->nullable(); // colors, bg, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_tiers');
    }
};
