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
        Schema::create('landlord_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nrc_passport', 50)->unique();
            $table->string('address');
            $table->string('province', 100);
            $table->string('town', 100);
            $table->string('tier'); // small, medium, large
            $table->string('status')->default('pending'); // pending, under_review, approved, rejected
            $table->string('id_document_url');
            $table->string('proof_of_address_url');
            $table->string('tax_certificate_url')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landlord_applications');
    }
};
