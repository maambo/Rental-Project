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
        Schema::table('property_applications', function (Blueprint $table) {
            $table->timestamp('review_started_at')->nullable()->after('status');
            $table->timestamp('payment_requested_at')->nullable()->after('review_started_at');
            $table->timestamp('completed_at')->nullable()->after('payment_requested_at');
            $table->text('rejection_reason')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('property_applications', function (Blueprint $table) {
            $table->dropColumn(['review_started_at', 'payment_requested_at', 'completed_at', 'rejection_reason']);
        });
    }
};
