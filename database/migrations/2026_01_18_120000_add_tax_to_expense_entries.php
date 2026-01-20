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
        Schema::table('operating_expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('operating_expenses', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('vat_amount');
            }
            if (!Schema::hasColumn('operating_expenses', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
            }
        });

        Schema::table('non_operating_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('non_operating_entries', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('non_operating_entries', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operating_expenses', function (Blueprint $table) {
            if (Schema::hasColumn('operating_expenses', 'tax_amount')) {
                $table->dropColumn('tax_amount');
            }
            if (Schema::hasColumn('operating_expenses', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });

        Schema::table('non_operating_entries', function (Blueprint $table) {
            if (Schema::hasColumn('non_operating_entries', 'tax_amount')) {
                $table->dropColumn('tax_amount');
            }
            if (Schema::hasColumn('non_operating_entries', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });
    }
};
