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
            $table->boolean('is_visible_in_search')->default(false);
            $table->integer('report_count')->default(0);
            $table->boolean('is_auto_suspended')->default(false);
            $table->boolean('requires_real_time_photo')->default(false);
            $table->string('real_time_photo_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'is_visible_in_search',
                'report_count',
                'is_auto_suspended',
                'requires_real_time_photo',
                'real_time_photo_url',
            ]);
        });
    }
};
