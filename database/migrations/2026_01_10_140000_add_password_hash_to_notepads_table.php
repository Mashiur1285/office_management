<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notepads', function (Blueprint $table) {
            if (!Schema::hasColumn('notepads', 'password_hash')) {
                $table->string('password_hash')->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notepads', function (Blueprint $table) {
            if (Schema::hasColumn('notepads', 'password_hash')) {
                $table->dropColumn('password_hash');
            }
        });
    }
};
