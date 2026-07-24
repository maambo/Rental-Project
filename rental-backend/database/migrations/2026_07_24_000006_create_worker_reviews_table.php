<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_booking_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('worker_profile_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->text('comment')->nullable();
            $table->text('worker_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_reviews');
    }
};
