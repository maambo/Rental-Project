<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->text('terms_and_conditions')->nullable()->after('description');
        });

        Schema::table('property_applications', function (Blueprint $table) {
            $table->text('applicant_terms')->nullable()->after('additional_comments');
            $table->timestamp('landlord_agreed_terms_at')->nullable()->after('applicant_terms');
            $table->timestamp('tenant_agreed_terms_at')->nullable()->after('landlord_agreed_terms_at');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('terms_and_conditions');
        });

        Schema::table('property_applications', function (Blueprint $table) {
            $table->dropColumn(['applicant_terms', 'landlord_agreed_terms_at', 'tenant_agreed_terms_at']);
        });
    }
};
