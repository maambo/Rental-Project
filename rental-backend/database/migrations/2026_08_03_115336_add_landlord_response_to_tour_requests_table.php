<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_requests', function (Blueprint $table) {
            $table->text('landlord_response')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tour_requests', function (Blueprint $table) {
            $table->dropColumn('landlord_response');
        });
    }
};
