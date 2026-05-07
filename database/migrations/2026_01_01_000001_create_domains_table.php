<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();

            // Plain integer — no foreign key constraint to avoid
            // "Failed to open referenced table 'users'" on some MySQL setups.
            $table->unsignedBigInteger('user_id');

            // Stored as lowercase, stripped of scheme and trailing slash.
            // e.g. "example.com" or "sub.example.com"
            $table->string('domain', 253);

            // HMAC-SHA256 hex token, 64 chars
            $table->string('verification_token', 64)->unique();

            // null = pending, true = verified
            $table->boolean('verified')->default(false);

            // Which method was used to verify (meta | dns | null)
            $table->string('verified_via', 10)->nullable();

            $table->timestamps();

            // One user cannot register the same domain twice
            $table->unique(['user_id', 'domain']);

            // Fast lookup by domain across all users
            $table->index('domain');

            // Index for fast per-user queries
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};