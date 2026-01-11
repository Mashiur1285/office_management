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
            $table->boolean('vat_paid')->default(false)->after('notes');
            $table->string('vat_chalan_number')->nullable()->after('vat_paid');
            $table->date('vat_payment_date')->nullable()->after('vat_chalan_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['vat_paid', 'vat_chalan_number', 'vat_payment_date']);
        });
    }
};
