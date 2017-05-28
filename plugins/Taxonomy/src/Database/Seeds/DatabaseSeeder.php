<?php

namespace Taxonomy\Database\Seeds;

use Mini\Database\Seeder;
use Mini\Database\ORM\Model;


class DatabaseSeeder extends Seeder
{
	/**
	 * Run the Database Seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		// $this->call('Taxonomy\Database\Seeds\FoobarTableSeeder');
	}
}
