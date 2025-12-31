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
        Schema::table('income_entries', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('accounting_period_id')->constrained('clients')->onDelete('cascade');
            $table->index(['accounting_period_id', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('income_entries', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropIndex(['accounting_period_id', 'client_id']);
            $table->dropColumn('client_id');
        });
    }
};
