<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_holder_types', function (Blueprint $table) {
            $table->id();
            $table->string('value')->unique();
            $table->string('label');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        DB::table('document_holder_types')->insert([
            [
                'value' => 'agency_user',
                'label' => 'MITT Staff Member',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 'bd_company',
                'label' => 'BD Processing Company',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('document_holder_types');
    }
};
