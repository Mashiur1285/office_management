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
            // Remove redundant agent fields
            $table->dropColumn(['agent_name', 'agent_reference_number']);

            // Add agent address field
            $table->text('agent_address')->nullable()->after('agent_mobile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Restore removed fields
            $table->string('agent_name')->nullable();
            $table->string('agent_reference_number')->nullable();

            // Remove agent address
            $table->dropColumn('agent_address');
        });
    }
};
