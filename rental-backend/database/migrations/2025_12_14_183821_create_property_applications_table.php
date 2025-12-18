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
        Schema::create('property_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Employment
            $table->string('employer_name');
            $table->string('job_title');
            $table->decimal('monthly_income', 10, 2);
            $table->string('employment_duration');

            // References
            $table->string('reference_1_name');
            $table->string('reference_1_phone');
            $table->string('reference_1_relation');
            $table->string('reference_2_name');
            $table->string('reference_2_phone');
            $table->string('reference_2_relation');

            // Lease Details
            $table->date('preferred_move_in_date');
            $table->integer('lease_duration_months');
            $table->integer('occupants_count');
            $table->boolean('has_pets')->default(false);
            $table->string('pet_details')->nullable();
            $table->text('additional_comments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_applications');
    }
};
