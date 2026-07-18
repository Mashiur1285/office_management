<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Gap (in inches) printed above the letterhead — useful for pre-printed pads.
            $table->decimal('letterhead_top_gap', 5, 2)->default(0)->after('letterhead_enabled');

            // Optional full-width header banner image (used instead of the composed header).
            $table->string('header_image_path')->nullable()->after('letterhead_top_gap');

            // Watermark can be text or an uploaded image.
            $table->string('watermark_type')->default('text')->after('watermark_text');
            $table->string('watermark_image_path')->nullable()->after('watermark_type');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'letterhead_top_gap',
                'header_image_path',
                'watermark_type',
                'watermark_image_path',
            ]);
        });
    }
};
