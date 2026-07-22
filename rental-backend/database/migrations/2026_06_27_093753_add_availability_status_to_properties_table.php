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
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('availability_status', ['available', 'rented', 'sold'])
                  ->default('available')
                  ->after('approval_status');
            $table->timestamp('availability_changed_at')->nullable()->after('availability_status');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['availability_status', 'availability_changed_at']);
        });
    }
};
