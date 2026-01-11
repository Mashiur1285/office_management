<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE income_entries MODIFY description TEXT NULL');
        DB::statement('ALTER TABLE cost_of_sales MODIFY description TEXT NULL');
        DB::statement('ALTER TABLE operating_expenses MODIFY description TEXT NULL');
        DB::statement('ALTER TABLE non_operating_entries MODIFY description TEXT NULL');
        DB::statement('ALTER TABLE tax_entries MODIFY description TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE income_entries SET description = "" WHERE description IS NULL');
        DB::statement('UPDATE cost_of_sales SET description = "" WHERE description IS NULL');
        DB::statement('UPDATE operating_expenses SET description = "" WHERE description IS NULL');
        DB::statement('UPDATE non_operating_entries SET description = "" WHERE description IS NULL');
        DB::statement('UPDATE tax_entries SET description = "" WHERE description IS NULL');

        DB::statement('ALTER TABLE income_entries MODIFY description TEXT NOT NULL');
        DB::statement('ALTER TABLE cost_of_sales MODIFY description TEXT NOT NULL');
        DB::statement('ALTER TABLE operating_expenses MODIFY description TEXT NOT NULL');
        DB::statement('ALTER TABLE non_operating_entries MODIFY description TEXT NOT NULL');
        DB::statement('ALTER TABLE tax_entries MODIFY description TEXT NOT NULL');
    }
};
