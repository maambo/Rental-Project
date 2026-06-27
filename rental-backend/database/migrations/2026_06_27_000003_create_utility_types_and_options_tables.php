<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();        // e.g. Power, Water
            $table->string('icon')->nullable();      // e.g. bolt, water-drop (icon name)
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('utility_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utility_type_id')->constrained()->cascadeOnDelete();
            $table->string('label');                // e.g. ZESCO
            $table->string('value');                // e.g. zesco (slugified)
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['utility_type_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_options');
        Schema::dropIfExists('utility_types');
    }
};
