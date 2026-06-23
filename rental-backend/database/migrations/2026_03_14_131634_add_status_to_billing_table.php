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
        Schema::table('billing', function (Blueprint $table) {
            if (!Schema::hasColumn('billing', 'status')) {
                $table->string('status')->default('pending')->after('lease_agreement_id');
            }
            if (!Schema::hasColumn('billing', 'proof_of_payment')) {
                $table->string('proof_of_payment')->nullable()->after('status');
            }
            if (!Schema::hasColumn('billing', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('proof_of_payment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing', function (Blueprint $table) {
            $table->dropColumn(['status', 'proof_of_payment', 'paid_at']);
        });
    }
};
