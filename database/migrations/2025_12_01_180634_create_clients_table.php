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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->string('nid_number')->unique();
            $table->string('nid_file_path')->nullable();
            $table->string('passport_number')->unique();
            $table->string('passport_file_path')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('mobile')->nullable();
            $table->string('district')->nullable();
            $table->text('address')->nullable();

            $table->string('job_sector')->nullable();
            $table->string('job_sub_sector')->nullable();

            $table->string('foreign_company_country')->nullable();
            $table->string('foreign_company_name')->nullable();
            $table->string('foreign_company_email')->nullable();
            $table->string('foreign_company_phone')->nullable();

            $table->string('agent_name')->nullable();
            $table->string('agent_reference_number')->nullable();
            $table->string('agent_mobile')->nullable();

            $table->decimal('medical_fee', 12, 2)->nullable();
            $table->string('medical_report_path')->nullable();
            $table->string('fit_status')->default('pending');
            $table->string('fit_report_path')->nullable();

            $table->string('documents_submitted_to')->nullable();
            $table->string('documents_submission_phone')->nullable();
            $table->date('date_of_submission')->nullable();
            $table->date('expected_date_to_collect')->nullable();
            $table->date('documents_collected_date')->nullable();

            $table->decimal('total_fee', 12, 2)->default(0);
            $table->decimal('current_due', 12, 2)->default(0);
            $table->decimal('partial_paid_amount', 12, 2)->default(0);
            $table->date('partial_payment_date')->nullable();
            $table->decimal('next_payment_amount', 12, 2)->default(0);
            $table->date('next_payment_date')->nullable();
            $table->decimal('final_payment', 12, 2)->default(0);
            $table->date('final_payment_date')->nullable();

            $table->string('online_status')->default('pending');
            $table->string('calling_status')->default('pending');
            $table->string('evisa_status')->default('pending');
            $table->string('overall_status')->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
