<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('promos', function (Blueprint $table) {
        $table->unsignedTinyInteger('day_of_week')->nullable()->after('price'); 
        // nullable para no romper tus promos actuales
    });
}

public function down()
{
    Schema::table('promos', function (Blueprint $table) {
        $table->dropColumn('day_of_week');
    });
}


};
