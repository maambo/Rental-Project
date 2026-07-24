<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('worker_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('worker_service_id')->nullable()->constrained()->onDelete('set null');

            $table->string('job_description');
            $table->string('location')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time', 10)->nullable();

            // Financials
            $table->decimal('agreed_price', 10, 2)->nullable();
            $table->decimal('platform_fee', 10, 2)->default(0.00);
            $table->decimal('worker_net', 10, 2)->default(0.00);

            // Workflow
            $table->enum('status', [
                'pending',      // client submitted
                'accepted',     // worker accepted
                'rejected',     // worker rejected
                'in_progress',  // work started
                'completed',    // worker marked done
                'cancelled',    // client cancelled
                'disputed',
            ])->default('pending');

            $table->text('client_notes')->nullable();
            $table->text('worker_notes')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_bookings');
    }
};
