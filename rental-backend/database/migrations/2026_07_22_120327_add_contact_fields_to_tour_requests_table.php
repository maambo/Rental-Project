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
        Schema::table('tour_requests', function (Blueprint $table) {
            $table->string('name')->nullable()->after('property_id');
            $table->string('email')->nullable()->after('name');
            $table->date('preferred_date')->nullable()->after('scheduled_at');
            $table->string('preferred_time', 10)->nullable()->after('preferred_date');
        });
    }

    public function down(): void
    {
        Schema::table('tour_requests', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'preferred_date', 'preferred_time']);
        });
    }
};
