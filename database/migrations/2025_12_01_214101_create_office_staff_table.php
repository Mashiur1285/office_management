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
        Schema::create('office_staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('designation')->nullable(); // e.g., Document Officer, Manager, etc.
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('nid_number')->nullable();
            $table->text('address')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_staff');
    }
};
