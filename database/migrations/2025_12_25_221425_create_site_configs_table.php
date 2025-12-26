<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteConfigsTable extends Migration
{
    public function up()
    {
        Schema::create('site_configs', function (Blueprint $table) {
            $table->id();
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->json('open_days')->nullable();     // array ints 0..6
            $table->json('closed_dates')->nullable();  // array YYYY-MM-DD
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_configs');
    }
}
