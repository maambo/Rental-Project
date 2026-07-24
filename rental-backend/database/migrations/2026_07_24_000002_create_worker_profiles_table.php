<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('trade_category_id')->constrained()->onDelete('restrict');
            $table->foreignId('town_id')->nullable()->constrained()->onDelete('set null');

            $table->string('tagline', 120)->nullable();
            $table->text('bio')->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->string('profile_photo_url')->nullable();

            // Identity / verification
            $table->string('nrc_url')->nullable();
            $table->string('certificate_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');

            // Service area & contact
            $table->unsignedSmallInteger('service_radius_km')->default(20);
            $table->string('phone', 20)->nullable();

            // Ratings (denormalised, updated on each new review)
            $table->decimal('rating_average', 3, 2)->default(0.00);
            $table->unsignedInteger('rating_count')->default(0);

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_profiles');
    }
};
