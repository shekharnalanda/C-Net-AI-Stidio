<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_jobs', function (Blueprint $table) {
            $table->unsignedInteger('priority')->default(100)->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->string('engine')->nullable()->index();
            $table->string('queue_name')->default('default')->index();
            $table->timestamp('available_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('ai_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'priority',
                'attempts',
                'engine',
                'queue_name',
                'available_at',
            ]);
        });
    }
};
