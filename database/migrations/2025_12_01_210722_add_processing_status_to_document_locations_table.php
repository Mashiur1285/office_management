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
        Schema::table('document_locations', function (Blueprint $table) {
            $table->string('processing_status')->nullable()->after('holder_id'); // pending, accepted, rejected, completed
            $table->text('processing_notes')->nullable()->after('processing_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_locations', function (Blueprint $table) {
            $table->dropColumn(['processing_status', 'processing_notes']);
        });
    }
};
