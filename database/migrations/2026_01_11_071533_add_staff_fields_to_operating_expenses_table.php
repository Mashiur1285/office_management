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
            $table->foreignId('staff_id')->nullable()->after('client_id')->constrained('office_staff')->nullOnDelete();
            $table->decimal('salary_amount', 12, 2)->nullable()->after('description');
            $table->decimal('bonus_amount', 12, 2)->nullable()->after('salary_amount');
            $table->decimal('paid_amount', 12, 2)->nullable()->after('bonus_amount');
            $table->decimal('due_amount', 12, 2)->nullable()->after('paid_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operating_expenses', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropColumn(['staff_id', 'salary_amount', 'bonus_amount', 'paid_amount', 'due_amount']);
        });
    }
};
