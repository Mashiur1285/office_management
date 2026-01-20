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
        Schema::table('tax_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('tax_entries', 'staff_id')) {
                $table->foreignId('staff_id')->nullable()->after('client_id')->constrained('office_staff')->onDelete('cascade');
                $table->index(['accounting_period_id', 'staff_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_entries', function (Blueprint $table) {
            if (Schema::hasColumn('tax_entries', 'staff_id')) {
                $table->dropForeign(['staff_id']);
                $table->dropIndex(['accounting_period_id', 'staff_id']);
                $table->dropColumn('staff_id');
            }
        });
    }
};
