<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the CHECK constraint on category column
        DB::statement('ALTER TABLE non_operating_entries DROP CONSTRAINT IF EXISTS non_operating_entries_category_check');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the check constraint only if there are no violating rows
        // First, check if constraint already exists
        $constraintExists = DB::select("
            SELECT constraint_name
            FROM information_schema.table_constraints
            WHERE table_name = 'non_operating_entries'
            AND constraint_name = 'non_operating_entries_category_check'
        ");

        if (empty($constraintExists)) {
            DB::statement("
                ALTER TABLE non_operating_entries
                ADD CONSTRAINT non_operating_entries_category_check
                CHECK (category IN ('interest_income', 'interest_expense', 'forex_gain', 'forex_loss', 'penalties', 'legal_fees', 'other'))
            ");
        }
    }
};
