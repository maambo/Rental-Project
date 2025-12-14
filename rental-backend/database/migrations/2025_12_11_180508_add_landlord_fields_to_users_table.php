<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Landlord-specific fields
            $table->enum('role', ['admin', 'landlord', 'tenant'])->default('tenant')->after('email');
            $table->enum('landlord_tier', ['small', 'medium', 'large'])->nullable()->after('role');
            $table->enum('application_status', ['pending', 'under_review', 'approved', 'rejected'])->nullable()->after('landlord_tier');
            $table->timestamp('approved_date')->nullable()->after('application_status');
            $table->integer('property_count')->default(0)->after('approved_date');
            
            // Google OAuth fields
            $table->string('google_id')->nullable()->unique()->after('property_count');
            $table->string('avatar')->nullable()->after('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'landlord_tier',
                'application_status',
                'approved_date',
                'property_count',
                'google_id',
                'avatar',
            ]);
        });
    }
};
