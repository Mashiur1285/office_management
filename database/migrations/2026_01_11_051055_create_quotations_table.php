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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_no')->unique();
            $table->unsignedInteger('quotation_year');
            $table->unsignedInteger('sequence');
            $table->date('quotation_date');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('client_name');
            $table->string('organization_name')->nullable();
            $table->string('client_mobile')->nullable();
            $table->string('client_email')->nullable();
            $table->string('service_category');
            $table->string('service_type');
            $table->text('description');
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->text('company_address')->nullable();
            $table->foreignId('quotation_maker_id')->nullable()->constrained('office_staff')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('terms_type')->default('default');
            $table->text('terms_text')->nullable();
            $table->date('valid_until');
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->unique(['quotation_year', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
