<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_version_id')->constrained()->onDelete('cascade');
            $table->string('original_name', 255);
            $table->string('stored_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('sha256', 64);
            $table->string('sha512', 128)->nullable();
            $table->enum('verification_status', ['pending', 'valid', 'invalid'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('package_version_id');
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_files');
    }
};
