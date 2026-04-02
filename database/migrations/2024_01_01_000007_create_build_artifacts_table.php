<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('build_artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['zip', 'manifest']);
            $table->string('file_path', 500);
            $table->unsignedBigInteger('file_size');
            $table->string('sha256', 64);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('build_artifacts');
    }
};
