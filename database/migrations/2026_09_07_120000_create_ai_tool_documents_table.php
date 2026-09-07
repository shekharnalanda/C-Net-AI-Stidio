<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tool_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('project_id')->nullable()->index();
            $table->string('tool_type', 50)->index();
            $table->string('title', 150);
            $table->string('language', 10)->default('en');
            $table->string('status', 30)->default('draft')->index();
            $table->longText('source_text')->nullable();
            $table->json('content')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tool_documents');
    }
};
