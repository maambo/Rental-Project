<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlord_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tier', ['small', 'medium', 'large']);
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            
            // Documents
            $table->string('id_document_url')->nullable();
            $table->string('proof_of_address_url')->nullable();
            $table->string('tax_certificate_url')->nullable();
            
            // Admin review
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_applications');
    }
};
