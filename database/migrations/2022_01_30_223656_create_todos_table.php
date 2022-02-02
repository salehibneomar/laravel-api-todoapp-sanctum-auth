<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->boolean('status')->default(0)->comment('0=not done, 1=done');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedMediumInteger('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('todos');
    }
}
