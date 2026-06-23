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
        Schema::create('lease_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Tenant
            $table->foreignId('landlord_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('draft'); // draft, pending_signature, signed, approved, active, terminated, completed
            $table->text('content')->nullable();
            $table->decimal('monthly_rent', 12, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamp('tenant_signed_at')->nullable();
            $table->timestamp('landlord_signed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_agreements');
    }
};
