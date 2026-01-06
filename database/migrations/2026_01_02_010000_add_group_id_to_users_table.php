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
        Schema::table('users', function (Blueprint $table) {
            $table->string('group_id')->default('default')->after('role');
            $table->index('group_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->unique(['username', 'group_id'], 'users_username_group_unique');

            $table->dropUnique(['email']);
            $table->unique(['email', 'group_id'], 'users_email_group_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_group_unique');
            $table->dropUnique('users_email_group_unique');

            $table->unique('username');
            $table->unique('email');

            $table->dropIndex(['group_id']);
            $table->dropColumn('group_id');
        });
    }
};
