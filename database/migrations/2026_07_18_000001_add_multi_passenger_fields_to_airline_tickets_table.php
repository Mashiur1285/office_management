<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airline_tickets', function (Blueprint $table) {
            // Reservation / guest PNR alongside the existing airline PNR.
            $table->string('reservation_pnr')->nullable()->after('pnr');

            // Extra passengers under the same PNR: [{passenger_name, passport_number, ticket_number}]
            $table->json('additional_passengers')->nullable()->after('reservation_pnr');
        });
    }

    public function down(): void
    {
        Schema::table('airline_tickets', function (Blueprint $table) {
            $table->dropColumn(['reservation_pnr', 'additional_passengers']);
        });
    }
};
