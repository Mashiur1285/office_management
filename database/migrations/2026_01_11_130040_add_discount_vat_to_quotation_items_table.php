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
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->string('discount_type')->default('percent')->after('price');
            $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_value');
            $table->decimal('vat_rate', 5, 2)->default(0)->after('discount_amount');
            $table->decimal('vat_amount', 10, 2)->default(0)->after('vat_rate');
            $table->decimal('line_total', 12, 2)->default(0)->after('vat_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_value',
                'discount_amount',
                'vat_rate',
                'vat_amount',
                'line_total',
            ]);
        });
    }
};
