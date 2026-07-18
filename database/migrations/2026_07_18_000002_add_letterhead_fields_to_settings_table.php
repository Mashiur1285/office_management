<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('company_address')->nullable()->after('logo_path');
            $table->string('company_phone')->nullable()->after('company_address');
            $table->string('company_email')->nullable()->after('company_phone');
            $table->string('company_tagline')->nullable()->after('company_email');
            $table->text('footer_note')->nullable()->after('company_tagline');
            $table->boolean('letterhead_enabled')->default(true)->after('footer_note');
            $table->boolean('watermark_enabled')->default(false)->after('letterhead_enabled');
            $table->string('watermark_text')->nullable()->after('watermark_enabled');
        });

        // Seed the existing hardcoded letterhead values so current PDFs stay identical.
        \DB::table('settings')->where('id', 1)->update([
            'company_address' => '25,26,27, Kazi Nazrul Islam Avenue, Banglamotor Trade Center, (Former Happy Rahman Plaza) 4th Floor, Banglamotor Shahbagh, Dhaka-1000.',
            'company_phone'   => '+88 01716 864 109 / +88 01332 502 234',
            'company_email'   => 'zulia.tourstravelsbd@gmail.com',
            'company_tagline' => 'IATA Accredited | ATAB Member | Official Quotation & Reports',
        ]);
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'company_address',
                'company_phone',
                'company_email',
                'company_tagline',
                'footer_note',
                'letterhead_enabled',
                'watermark_enabled',
                'watermark_text',
            ]);
        });
    }
};
