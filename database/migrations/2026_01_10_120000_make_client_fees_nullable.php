<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE clients MODIFY total_fee DECIMAL(12,2) NULL');
        DB::statement('ALTER TABLE clients MODIFY current_due DECIMAL(12,2) NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE clients SET total_fee = 0 WHERE total_fee IS NULL');
        DB::statement('UPDATE clients SET current_due = 0 WHERE current_due IS NULL');
        DB::statement('ALTER TABLE clients MODIFY total_fee DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE clients MODIFY current_due DECIMAL(12,2) NOT NULL DEFAULT 0');
    }
};
