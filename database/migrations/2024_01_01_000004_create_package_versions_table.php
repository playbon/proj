<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('version', 50);
            $table->text('changelog')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('package_id');
            $table->unique(['package_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_versions');
    }
};
