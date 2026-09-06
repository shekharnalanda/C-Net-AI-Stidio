<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_workers', function (Blueprint $table) {

            if (!Schema::hasColumn('ai_workers', 'worker_uuid')) {
                $table->string('worker_uuid', 64)->nullable()->unique();
            }

            if (!Schema::hasColumn('ai_workers', 'name')) {
                $table->string('name')->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'platform')) {
                $table->string('platform')->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'hostname')) {
                $table->string('hostname')->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'ip_address')) {
                $table->string('ip_address', 64)->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'cpu')) {
                $table->text('cpu')->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'gpu')) {
                $table->text('gpu')->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'ram_mb')) {
                $table->unsignedBigInteger('ram_mb')->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'disk_free_mb')) {
                $table->unsignedBigInteger('disk_free_mb')->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'ffmpeg_version')) {
                $table->string('ffmpeg_version')->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'capabilities')) {
                $table->json('capabilities')->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'current_job_id')) {
                $table->unsignedBigInteger('current_job_id')->nullable();
            }

            if (!Schema::hasColumn('ai_workers', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->index();
            }

            if (!Schema::hasColumn('ai_workers', 'registered_at')) {
                $table->timestamp('registered_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        //
    }
};
