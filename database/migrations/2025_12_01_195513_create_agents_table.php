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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nid_number')->nullable();
            $table->string('nid_file_path')->nullable();
            $table->string('district')->nullable();
            $table->string('bank_details')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->json('services')->nullable(); // e.g., ["Visa Processing", "Medical", "Job Offer Letter"]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
