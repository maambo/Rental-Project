<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_portfolio_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_profile_id')->constrained()->onDelete('cascade');
            $table->string('image_url');
            $table->string('caption', 120)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_portfolio_photos');
    }
};
