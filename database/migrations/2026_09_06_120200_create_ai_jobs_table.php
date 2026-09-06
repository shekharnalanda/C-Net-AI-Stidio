<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('job_uuid')->unique();
            $table->foreignId('user_id')->nullable()->index();
            $table->foreignId('project_id')->nullable()->index();
            $table->string('job_type')->index();
            $table->string('status')->default('queued')->index();
            $table->string('worker_id')->nullable()->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_jobs');
    }
};
