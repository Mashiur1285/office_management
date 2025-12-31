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
        Schema::table('clients', function (Blueprint $table) {
            // Check if old columns exist before dropping
            if (Schema::hasColumn('clients', 'online_status')) {
                $table->dropColumn(['online_status', 'calling_status', 'evisa_status']);
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            // Check if we need to rename overall_status to visa_status
            if (Schema::hasColumn('clients', 'overall_status') && !Schema::hasColumn('clients', 'visa_status')) {
                $table->renameColumn('overall_status', 'visa_status');
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            // Add visa_stage if it doesn't exist
            if (!Schema::hasColumn('clients', 'visa_stage')) {
                $table->string('visa_stage')->default('document_collection')->after('visa_status');
                // Possible values: document_collection, medical_processing, bd_company_processing,
                // online_submission, calling_stage, evisa_stage, approved, rejected
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Restore original columns
            $table->string('online_status')->default('pending');
            $table->string('calling_status')->default('pending');
            $table->string('evisa_status')->default('pending');

            // Rename back
            $table->renameColumn('visa_status', 'overall_status');

            // Remove visa_stage
            $table->dropColumn('visa_stage');
        });
    }
};
