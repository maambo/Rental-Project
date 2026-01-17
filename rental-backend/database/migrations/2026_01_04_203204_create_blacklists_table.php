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
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->string('nrc_passport')->unique();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->text('reason');
            $table->foreignId('blacklisted_by')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['fraud', 'harassment', 'fake_listings', 'other']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};
