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
        Schema::create('vat_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_period_id')->constrained()->onDelete('cascade');
            $table->decimal('payment_amount', 15, 2);
            $table->string('chalan_number')->nullable();
            $table->string('chalan_slip')->nullable(); // File path for uploaded receipt
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vat_payments');
    }
};
