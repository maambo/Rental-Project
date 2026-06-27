<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_utilities', function (Blueprint $table) {
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('utility_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('utility_option_id')->nullable()->constrained()->nullOnDelete();
            $table->primary(['property_id', 'utility_type_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_utilities');
    }
};
