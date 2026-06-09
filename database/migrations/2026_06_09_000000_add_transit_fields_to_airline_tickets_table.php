<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airline_tickets', function (Blueprint $table) {
            $table->string('passport_number')->nullable()->after('passenger_phone');
            $table->date('issue_date')->nullable()->after('ticket_number');
            $table->date('arrival_date')->nullable()->after('departure_time');
            $table->time('arrival_time')->nullable()->after('arrival_date');
            $table->boolean('has_transit')->default(false)->after('arrival_time');
            $table->json('transits')->nullable()->after('has_transit');
        });
    }

    public function down(): void
    {
        Schema::table('airline_tickets', function (Blueprint $table) {
            $table->dropColumn(['passport_number', 'issue_date', 'arrival_date', 'arrival_time', 'has_transit', 'transits']);
        });
    }
};
