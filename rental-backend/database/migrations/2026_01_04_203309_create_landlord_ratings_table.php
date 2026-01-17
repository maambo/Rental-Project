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
        if (!Schema::hasTable('landlord_ratings')) {
            Schema::create('landlord_ratings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('landlord_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
                $table->integer('rating'); // 1-5 stars
                $table->text('review')->nullable();
                $table->boolean('is_verified_rental')->default(false); // true if viewing was confirmed
                $table->timestamps();
                
                $table->unique(['landlord_id', 'tenant_id', 'property_id']); // one rating per rental
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landlord_ratings');
    }
};
