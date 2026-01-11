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
        Schema::create('tax_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('payment_type', ['bulk', 'individual'])->default('bulk');
            $table->decimal('payment_amount', 15, 2);
            $table->string('chalan_number')->nullable();
            $table->string('chalan_slip')->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['accounting_period_id', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_payments');
    }
};
