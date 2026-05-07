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
        if (! Schema::hasTable('face_widgets')) {
            return;
        }

        Schema::table('face_widgets', function (Blueprint $table) {
            if (! Schema::hasColumn('face_widgets', 'widget_auth_type')) {
                $table->enum('widget_auth_type', ['register', 'login'])->default('register')->after('mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('face_widgets')) {
            return;
        }

        Schema::table('face_widgets', function (Blueprint $table) {
            $table->dropColumn('widget_auth_type');
        });
    }
};
