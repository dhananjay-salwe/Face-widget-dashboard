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
            if (! Schema::hasColumn('face_widgets', 'position')) {
                $table->string('position')->nullable()->after('mode');
            }

            if (! Schema::hasColumn('face_widgets', 'allowed_pages')) {
                $table->text('allowed_pages')->nullable()->after('allowed_domains');
            }

            if (! Schema::hasColumn('face_widgets', 'welcome_title')) {
                $table->string('welcome_title')->nullable()->after('api_hits');
            }

            if (! Schema::hasColumn('face_widgets', 'welcome_message')) {
                $table->text('welcome_message')->nullable()->after('welcome_title');
            }

            if (! Schema::hasColumn('face_widgets', 'button_text')) {
                $table->string('button_text')->nullable()->after('welcome_message');
            }

            if (! Schema::hasColumn('face_widgets', 'button_color')) {
                $table->string('button_color', 7)->nullable()->after('button_text');
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
            $table->dropColumn([
                'position',
                'allowed_pages',
                'welcome_title',
                'welcome_message',
                'button_text',
                'button_color',
            ]);
        });
    }
};

