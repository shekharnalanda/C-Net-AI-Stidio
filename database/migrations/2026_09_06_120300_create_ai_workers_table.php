<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_workers', function (Blueprint $table) {
            $table->id();
            $table->string('worker_uuid')->unique();
            $table->string('name');
            $table->string('status')->default('offline')->index();
            $table->string('platform')->nullable();
            $table->string('cpu')->nullable();
            $table->string('gpu')->nullable();
            $table->unsignedInteger('ram_mb')->nullable();
            $table->json('capabilities')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_workers');
    }
};
