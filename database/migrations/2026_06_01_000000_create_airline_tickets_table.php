<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('airline_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('passenger_name');
            $table->string('passenger_email');
            $table->string('passenger_phone')->nullable();
            $table->string('airline_name');
            $table->string('flight_number');
            $table->string('pnr')->nullable();
            $table->string('ticket_number')->nullable();
            $table->string('origin');
            $table->string('destination');
            $table->date('flight_date');
            $table->date('original_flight_date')->nullable();
            $table->time('departure_time')->nullable();
            $table->enum('status', ['confirmed', 'rescheduled', 'cancelled', 'flown'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airline_tickets');
    }
};
