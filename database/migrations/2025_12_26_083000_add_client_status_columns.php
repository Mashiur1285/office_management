<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'online_status')) {
                $table->string('online_status')->default('pending')->after('final_payment_date');
            }
            if (! Schema::hasColumn('clients', 'calling_status')) {
                $table->string('calling_status')->default('pending')->after('online_status');
            }
            if (! Schema::hasColumn('clients', 'evisa_status')) {
                $table->string('evisa_status')->default('pending')->after('calling_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'online_status')) {
                $table->dropColumn(['online_status', 'calling_status', 'evisa_status']);
            }
        });
    }
};
