<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_activation_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code_hash');
            $table->string('label')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('used_at')->nullable()->index();
            $table->unsignedBigInteger('worker_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_activation_codes');
    }
};
