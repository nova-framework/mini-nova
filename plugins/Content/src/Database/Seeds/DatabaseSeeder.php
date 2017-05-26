<?php

namespace Content\Database\Seeds;

use Mini\Database\ORM\Model;


class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        //
        //$this->call('Content\Database\Seeds\FoobarTableSeeder');
    }

}
