<?php

namespace FileManager\Database\Seeds;

use Mini\Database\ORM\Model;
use Mini\Database\Seeder;


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

        // $this->call('FileManager\Database\Seeds\FoobarTableSeeder');
    }
}
