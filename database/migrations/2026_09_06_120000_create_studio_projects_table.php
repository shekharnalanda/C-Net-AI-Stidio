<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('name');
            $table->string('type')->default('video');
            $table->string('status')->default('draft')->index();
            $table->string('aspect_ratio')->default('16:9');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->json('settings')->nullable();
            $table->json('timeline')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_projects');
    }
};
