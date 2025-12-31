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
        Schema::create('non_operating_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_period_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['income', 'expense']);
            $table->enum('category', [
                'interest_income',
                'interest_expense',
                'forex_gain',
                'forex_loss',
                'penalties',
                'legal_fees',
                'other'
            ]);
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['accounting_period_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_operating_entries');
    }
};
