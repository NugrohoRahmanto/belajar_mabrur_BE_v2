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
        Schema::table('host_groups', function (Blueprint $table) {
            $table->unsignedInteger('host_quota')->default(1)->after('is_default');
            $table->unsignedInteger('user_quota')->default(1)->after('host_quota');
            $table->text('description')->nullable()->after('user_quota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('host_groups', function (Blueprint $table) {
            $table->dropColumn(['host_quota', 'user_quota', 'description']);
        });
    }
};
