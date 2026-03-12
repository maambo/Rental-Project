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
        Schema::create('billing', function (Blueprint $table) {
            $table->id();
            $table->string('UserID', 50)->index();
            $table->float('Amount');
            $table->dateTime('Date')->useCurrent();
            $table->string('Description', 255);
            $table->integer('Year')->nullable();
            $table->foreignId('lease_agreement_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            $table->unique(['UserID', 'Year'], 'billing_unique_user_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing');
    }
};
