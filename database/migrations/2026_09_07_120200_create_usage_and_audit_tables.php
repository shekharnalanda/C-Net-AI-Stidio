<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('project_id')->nullable()->index();
            $table->foreignId('ai_job_id')->nullable()->index();
            $table->string('metric', 50)->index();
            $table->unsignedBigInteger('quantity')->default(1);
            $table->string('period_key', 10)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'metric', 'period_key']);
        });

        Schema::create('security_audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('event', 80)->index();
            $table->string('severity', 20)->default('info')->index();
            $table->string('ip_hash', 64)->nullable();
            $table->string('request_id', 64)->nullable()->index();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_audit_events');
        Schema::dropIfExists('studio_usage_events');
    }
};
