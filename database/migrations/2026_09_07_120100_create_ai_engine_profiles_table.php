<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_engine_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category', 40)->index();
            $table->string('driver', 50)->default('worker');
            $table->json('capabilities')->nullable();
            $table->json('configuration')->nullable();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        DB::table('ai_engine_profiles')->insert([
            ['key' => 'cnet-video-worker', 'name' => 'C-Net Video Engine', 'category' => 'video-generation', 'driver' => 'worker', 'capabilities' => json_encode(['text-to-video', 'business-ad', 'reel']), 'priority' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'cnet-image-worker', 'name' => 'C-Net Image Motion Engine', 'category' => 'image-animation', 'driver' => 'worker', 'capabilities' => json_encode(['image-to-video']), 'priority' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'cnet-audio-worker', 'name' => 'C-Net Voice Engine', 'category' => 'text-to-speech', 'driver' => 'worker', 'capabilities' => json_encode(['voiceover']), 'priority' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'cnet-caption-worker', 'name' => 'C-Net Caption Engine', 'category' => 'speech-to-text', 'driver' => 'worker', 'capabilities' => json_encode(['captions']), 'priority' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'cnet-ad-worker', 'name' => 'C-Net Advertisement Engine', 'category' => 'ad-generation', 'driver' => 'worker', 'capabilities' => json_encode(['business-ad']), 'priority' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'cnet-short-video-worker', 'name' => 'C-Net Short Video Engine', 'category' => 'short-video', 'driver' => 'worker', 'capabilities' => json_encode(['reel']), 'priority' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'cnet-media-worker', 'name' => 'C-Net Media Processing Engine', 'category' => 'media-processing', 'driver' => 'worker', 'capabilities' => json_encode(['video']), 'priority' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_engine_profiles');
    }
};
