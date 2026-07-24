<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_profile_id')->constrained()->onDelete('cascade');
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->enum('rate_type', ['hourly', 'per_job', 'per_day'])->default('per_job');
            $table->decimal('base_rate', 10, 2);
            $table->decimal('minimum_charge', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_services');
    }
};
