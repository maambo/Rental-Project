<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->enum('id_type', ['nrc', 'passport'])->default('nrc')->after('phone');
            $table->string('nrc_passport', 50)->nullable()->unique()->after('id_type');
            $table->string('id_document_url')->nullable()->after('nrc_passport');
            $table->string('selfie_url')->nullable()->after('id_document_url');
            $table->timestamp('identity_verified_at')->nullable()->after('selfie_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nrc_passport']);
            $table->dropColumn(['phone', 'id_type', 'nrc_passport', 'id_document_url', 'selfie_url', 'identity_verified_at']);
        });
    }
};
