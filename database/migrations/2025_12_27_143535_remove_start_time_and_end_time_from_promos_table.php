<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('promos', function (Blueprint $table) {
            if (Schema::hasColumn('promos', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('promos', 'end_time')) {
                $table->dropColumn('end_time');
            }
        });
    }

    public function down()
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });
    }
};