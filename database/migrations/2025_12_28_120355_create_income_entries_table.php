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
        Schema::create('income_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_period_id')->constrained()->onDelete('cascade');
            $table->enum('category', [
                'travel_tourism',
                'manpower_exporting',
                'student_package',
                'other_income'
            ]);
            $table->string('subcategory'); // e.g., 'air_ticket_service_charge', 'hotel_commission'
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->decimal('vat_rate', 5, 2)->default(0); // Percentage (0 or 15)
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['accounting_period_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_entries');
    }
};
