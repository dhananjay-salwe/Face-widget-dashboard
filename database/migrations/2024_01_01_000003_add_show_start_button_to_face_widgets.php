<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('face_widgets')) {
            return;
        }

        if (Schema::hasColumn('face_widgets', 'show_start_button')) {
            return;
        }

        Schema::table('face_widgets', function (Blueprint $table) {
            // true  = show the Start button (manual click-to-start)
            // false = hide button, camera auto-starts on widget load after 1.5s
            $table->boolean('show_start_button')->default(true)->after('button_color');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('face_widgets') || ! Schema::hasColumn('face_widgets', 'show_start_button')) {
            return;
        }

        Schema::table('face_widgets', function (Blueprint $table) {
            $table->dropColumn('show_start_button');
        });
    }
};
