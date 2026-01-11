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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->unsignedInteger('invoice_year');
            $table->unsignedInteger('sequence');
            $table->date('invoice_date');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('client_name');
            $table->string('organization_name')->nullable();
            $table->string('client_mobile')->nullable();
            $table->string('client_email')->nullable();
            $table->string('service_category');
            $table->string('service_type');
            $table->text('description')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->text('company_address')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->unique(['invoice_year', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
