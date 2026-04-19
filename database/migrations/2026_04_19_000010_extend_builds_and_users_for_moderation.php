<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE builds MODIFY COLUMN status ENUM('queued','processing','completed','failed','pending_review','blocked') NOT NULL DEFAULT 'queued'");
        }

        // Ban + first-build-reviewed fields on users
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('banned_until')->nullable()->after('last_seen_at');
            $table->unsignedTinyInteger('ban_count')->default(0)->after('banned_until');
            $table->boolean('publisher_reviewed')->default(false)->after('ban_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['banned_until', 'ban_count', 'publisher_reviewed']);
        });
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE builds MODIFY COLUMN status ENUM('queued','processing','completed','failed') NOT NULL DEFAULT 'queued'");
        }
    }
};
