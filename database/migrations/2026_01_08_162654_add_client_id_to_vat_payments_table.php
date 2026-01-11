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
        Schema::table('vat_payments', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('accounting_period_id')->constrained()->onDelete('cascade');
            $table->enum('payment_type', ['bulk', 'individual'])->default('bulk')->after('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vat_payments', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
            $table->dropColumn('payment_type');
        });
    }
};
