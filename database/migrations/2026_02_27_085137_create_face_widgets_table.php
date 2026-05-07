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
        Schema::create('face_widgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('mode'); // 'floating' or 'embedded'
            $table->string('theme_color')->default('#66b0ff');
            $table->text('allowed_domains')->nullable();
            $table->text('allowed_pages')->nullable();
            $table->integer('api_limit')->default(1000);
            $table->integer('api_hits')->default(0);
            $table->string('position')->nullable();
            $table->string('welcome_title')->nullable();
            $table->text('welcome_message')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_color', 7)->nullable();
            $table->boolean('show_start_button')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_widgets');
    }
};
