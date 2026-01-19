<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->default(1)->after('service_description');
            $table->decimal('unit_price', 12, 2)->default(0)->after('quantity');
        });

        DB::table('quotation_items')->update([
            'quantity' => 1,
            'unit_price' => DB::raw('price'),
        ]);
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit_price']);
        });
    }
};
