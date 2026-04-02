<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_artifact_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45);
            $table->timestamp('downloaded_at')->useCurrent();

            $table->index('build_artifact_id');
            $table->index('downloaded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_stats');
    }
};
