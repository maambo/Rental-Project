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
        Schema::table('landlord_applications', function (Blueprint $table) {
            $table->enum('verification_level', ['basic', 'trusted', 'premium'])->default('basic');
            $table->enum('landlord_type', ['private_landlord', 'agent'])->default('private_landlord');
            $table->string('selfie_url')->nullable();
            $table->string('video_walkthrough_url')->nullable();
            $table->string('business_registration_url')->nullable();
            $table->timestamp('verification_submitted_at')->nullable();
            $table->timestamp('verification_approved_at')->nullable();
            $table->enum('payment_status', ['unpaid', 'paid', 'expired'])->nullable();
            $table->timestamp('subscription_expires_at')->nullable();
            $table->integer('listing_limit')->default(5);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landlord_applications', function (Blueprint $table) {
            $table->dropColumn([
                'verification_level',
                'landlord_type',
                'selfie_url',
                'video_walkthrough_url',
                'business_registration_url',
                'verification_submitted_at',
                'verification_approved_at',
                'payment_status',
                'subscription_expires_at',
                'listing_limit',
            ]);
        });
    }
};
