<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'playbondibon@gmail.com')
            ->update(['role' => 'creator']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'playbondibon@gmail.com')
            ->where('role', 'creator')
            ->update(['role' => 'admin']);
    }
};
