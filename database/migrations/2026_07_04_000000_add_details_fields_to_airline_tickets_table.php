<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airline_tickets', function (Blueprint $table) {
            $table->string('ticket_class')->nullable()->after('ticket_number');

            // Luggage
            $table->string('hand_luggage_kg')->nullable()->after('ticket_class');
            $table->string('hand_luggage_max_weight')->nullable()->after('hand_luggage_kg');
            $table->string('cabin_luggage_kg')->nullable()->after('hand_luggage_max_weight');
            $table->string('cabin_luggage_max_weight')->nullable()->after('cabin_luggage_kg');

            $table->boolean('complementary_food')->default(false)->after('cabin_luggage_max_weight');

            // Airport / boarding
            $table->string('airport_name')->nullable()->after('destination');
            $table->string('terminal')->nullable()->after('airport_name');
            $table->string('gate')->nullable()->after('terminal');

            // Cancellation policy
            $table->unsignedInteger('free_cancellation_days')->nullable()->after('notes');
            $table->unsignedInteger('partial_cancellation_days')->nullable()->after('free_cancellation_days');
            $table->decimal('partial_cancellation_percent', 5, 2)->nullable()->after('partial_cancellation_days');
            $table->unsignedInteger('no_refund_hours')->nullable()->after('partial_cancellation_percent');
        });
    }

    public function down(): void
    {
        Schema::table('airline_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'ticket_class',
                'hand_luggage_kg',
                'hand_luggage_max_weight',
                'cabin_luggage_kg',
                'cabin_luggage_max_weight',
                'complementary_food',
                'airport_name',
                'terminal',
                'gate',
                'free_cancellation_days',
                'partial_cancellation_days',
                'partial_cancellation_percent',
                'no_refund_hours',
            ]);
        });
    }
};
