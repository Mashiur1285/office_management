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
            if (!Schema::hasColumn('clients', 'bd_company_id')) {
                $table->foreignId('bd_company_id')->nullable()->after('documents_submitted_to')->constrained('bd_companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('clients', 'foreign_company_id')) {
                $table->foreignId('foreign_company_id')->nullable()->after('bd_company_id')->constrained('foreign_companies')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'foreign_company_id')) {
                $table->dropConstrainedForeignId('foreign_company_id');
            }
            if (Schema::hasColumn('clients', 'bd_company_id')) {
                $table->dropConstrainedForeignId('bd_company_id');
            }
        });
    }
};
