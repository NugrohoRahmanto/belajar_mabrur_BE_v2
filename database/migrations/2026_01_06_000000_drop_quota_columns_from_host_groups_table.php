<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('host_groups', function (Blueprint $table) {
            if (Schema::hasColumn('host_groups', 'host_quota')) {
                $table->dropColumn('host_quota');
            }

            if (Schema::hasColumn('host_groups', 'user_quota')) {
                $table->dropColumn('user_quota');
            }
        });
    }

    public function down(): void
    {
        Schema::table('host_groups', function (Blueprint $table) {
            $table->unsignedInteger('host_quota')->default(1);
            $table->unsignedInteger('user_quota')->default(1);
        });
    }
};
