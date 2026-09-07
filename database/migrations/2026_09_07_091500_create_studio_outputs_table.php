<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_outputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('ai_job_id')->nullable()->index();

            $table->string('output_uuid')->unique();
            $table->string('name');
            $table->string('output_type')->default('video')->index();
            $table->string('format')->nullable();
            $table->string('status')->default('ready')->index();

            $table->text('path')->nullable();
            $table->text('source_path')->nullable();

            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('generated_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_outputs');
    }
};
