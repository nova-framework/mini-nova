<?php

use Mini\Database\Schema\Blueprint;
use Mini\Database\Migrations\Migration;


class CreateTermsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('terms', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('vocabulary_id')->unsigned();
			$table->string('name');
			$table->string('slug')->unique();
			$table->text('description');
			$table->integer('parent_id')->unsigned()->default(0);
			$table->integer('weight')->default(0);
			$table->timestamps();

			$table->foreign('vocabulary_id')->references('id')->on('vocabularies')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('terms');
	}
}
