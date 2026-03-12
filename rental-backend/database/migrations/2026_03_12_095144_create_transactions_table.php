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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('UID', 64);
            $table->string('RequestID', 64);
            $table->string('TransactionID', 64)->unique();
            $table->string('UserID', 64)->index();
            $table->string('NRC', 32)->nullable();
            $table->string('TransactionDate', 32);
            $table->double('Amount')->default(0);
            $table->string('Name', 255);
            $table->string('Type', 4)->nullable();
            $table->string('Hash', 128)->nullable();
            $table->string('Phone', 16)->nullable();
            $table->string('Status', 64);
            $table->string('Error', 64)->nullable();
            $table->longText('Data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
