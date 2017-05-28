<?php

use Mini\Database\Schema\Blueprint;
use Mini\Database\Migrations\Migration;


class CreateTermRelations extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('term_relations', function($table)
		{
			$table->increments('id');
			$table->integer('relationable_id')->unsigned();
			$table->string('relationable_type');
			$table->integer('term_id')->unsigned();
			$table->integer('vocabulary_id')->unsigned();
			$table->timestamps();

			$table->foreign('term_id')->references('id')->on('terms');
			$table->foreign('vocabulary_id')->references('id')->on('vocabularies');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('term_relations');
	}
}
