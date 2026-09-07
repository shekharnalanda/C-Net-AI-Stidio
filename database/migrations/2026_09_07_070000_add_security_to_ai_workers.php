<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_workers', function (Blueprint $table) {
            if (! Schema::hasColumn('ai_workers', 'token_hash')) {
                $table->string('token_hash')->nullable();
            }

            if (! Schema::hasColumn('ai_workers', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->index();
            }

            if (! Schema::hasColumn('ai_workers', 'credential_rotated_at')) {
                $table->timestamp('credential_rotated_at')->nullable();
            }

            if (! Schema::hasColumn('ai_workers', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
