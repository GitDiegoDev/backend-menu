<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('promos', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2)->nullable();
        $table->time('start_time'); // ← ¿Existe?
        $table->time('end_time');   // ← ¿Existe?
        $table->string('day_of_week')->default('todos');
        $table->boolean('active')->default(true);
        $table->string('image')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
