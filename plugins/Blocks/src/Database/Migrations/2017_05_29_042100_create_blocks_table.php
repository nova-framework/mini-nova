<?php

use Mini\Database\Schema\Blueprint;
use Mini\Database\Migrations\Migration;


class CreateBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blocks', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('area', 100)->nullable();
            $table->integer('weight')->default(0);
            $table->text('paths')->nullable();
            $table->tinyInteger('paths_mode')->unsigned()->default(0);
            $table->string('auth_mode', 10)->nullable();
            $table->text('user_roles')->nullable();
            $table->boolean('hide_title')->default(false);
            $table->string('handler')->nullable();
            $table->string('params')->nullable();
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
        Schema::dropIfExists('blocks');
    }
}
