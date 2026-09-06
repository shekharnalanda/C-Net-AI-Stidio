<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_jobs', function (Blueprint $table) {

            if (!Schema::hasColumn('ai_jobs', 'worker_id')) {
                $table->unsignedBigInteger('worker_id')->nullable()->index();
            }

            if (!Schema::hasColumn('ai_jobs', 'claimed_at')) {
                $table->timestamp('claimed_at')->nullable();
            }

            if (!Schema::hasColumn('ai_jobs', 'heartbeat_at')) {
                $table->timestamp('heartbeat_at')->nullable();
            }

            if (!Schema::hasColumn('ai_jobs', 'failed_at')) {
                $table->timestamp('failed_at')->nullable();
            }

            if (!Schema::hasColumn('ai_jobs', 'error_message')) {
                $table->text('error_message')->nullable();
            }

            if (!Schema::hasColumn('ai_jobs', 'output_path')) {
                $table->text('output_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
