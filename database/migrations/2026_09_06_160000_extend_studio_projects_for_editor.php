<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_projects', function (Blueprint $table) {
            $table->text('prompt')->nullable();
            $table->string('language')->default('en');
            $table->string('quality')->default('hd');
            $table->string('editor_mode')->default('smart');
            $table->json('brand_settings')->nullable();
            $table->json('generation_settings')->nullable();
            $table->timestamp('last_opened_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('studio_projects', function (Blueprint $table) {
            $table->dropColumn([
                'prompt',
                'language',
                'quality',
                'editor_mode',
                'brand_settings',
                'generation_settings',
                'last_opened_at',
            ]);
        });
    }
};
