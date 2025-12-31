<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to change the column type from enum to string
        DB::statement('ALTER TABLE non_operating_entries ALTER COLUMN category TYPE VARCHAR(255)');

        // Drop the old enum type if it exists
        DB::statement('DROP TYPE IF EXISTS non_operating_entries_category_old CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the enum constraint
        Schema::table('non_operating_entries', function (Blueprint $table) {
            $table->enum('category', [
                'interest_income',
                'interest_expense',
                'forex_gain',
                'forex_loss',
                'penalties',
                'legal_fees',
                'other'
            ])->change();
        });
    }
};
