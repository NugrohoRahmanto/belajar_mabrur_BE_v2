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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Authentication
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');

            // Role system: admin, host, user
            $table->enum('role', ['admin', 'host', 'user'])->default('user');

            // Token for API authentication
            $table->string('token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            // Track activity timestamp (monitor active users)
            $table->timestamp('last_active_at')->nullable();

            // Optional display name
            $table->string('name')->nullable();

            $table->rememberToken()->nullable();

            // Laravel default timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
