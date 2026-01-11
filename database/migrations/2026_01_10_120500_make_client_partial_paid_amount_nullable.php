<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE clients MODIFY partial_paid_amount DECIMAL(12,2) NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE clients SET partial_paid_amount = 0 WHERE partial_paid_amount IS NULL');
        DB::statement('ALTER TABLE clients MODIFY partial_paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0');
    }
};
