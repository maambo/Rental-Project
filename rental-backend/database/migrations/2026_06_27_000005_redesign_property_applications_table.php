<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_applications', function (Blueprint $table) {
            // Drop all old type-unaware columns
            $table->dropColumn([
                'employer_name',
                'job_title',
                'monthly_income',
                'employment_duration',
                'reference_1_name',
                'reference_1_phone',
                'reference_1_relation',
                'reference_2_name',
                'reference_2_phone',
                'reference_2_relation',
                'preferred_move_in_date',
                'lease_duration_months',
                'occupants_count',
                'has_pets',
                'pet_details',
                'additional_comments',
            ]);

            // Common fields
            $table->text('message')->nullable()->after('status');
            $table->date('preferred_move_in')->nullable()->after('message');
            $table->text('additional_comments')->nullable()->after('preferred_move_in');

            // Residential rent fields
            $table->unsignedTinyInteger('adults')->nullable()->after('additional_comments');
            $table->unsignedTinyInteger('children')->nullable()->after('adults');
            $table->boolean('has_pets')->default(false)->after('children');
            $table->string('pet_details')->nullable()->after('has_pets');

            // Commercial fields
            $table->string('intended_use')->nullable()->after('pet_details');
            $table->string('business_name')->nullable()->after('intended_use');
        });
    }

    public function down(): void
    {
        Schema::table('property_applications', function (Blueprint $table) {
            $table->dropColumn([
                'message', 'preferred_move_in', 'additional_comments',
                'adults', 'children', 'has_pets', 'pet_details',
                'intended_use', 'business_name',
            ]);
            $table->string('employer_name')->nullable();
            $table->string('job_title')->nullable();
            $table->decimal('monthly_income', 10, 2)->nullable();
            $table->string('employment_duration')->nullable();
            $table->string('reference_1_name')->nullable();
            $table->string('reference_1_phone')->nullable();
            $table->string('reference_1_relation')->nullable();
            $table->string('reference_2_name')->nullable();
            $table->string('reference_2_phone')->nullable();
            $table->string('reference_2_relation')->nullable();
            $table->date('preferred_move_in_date')->nullable();
            $table->integer('lease_duration_months')->nullable();
            $table->integer('occupants_count')->nullable();
            $table->boolean('has_pets')->default(false);
            $table->string('pet_details')->nullable();
            $table->text('additional_comments')->nullable();
        });
    }
};
