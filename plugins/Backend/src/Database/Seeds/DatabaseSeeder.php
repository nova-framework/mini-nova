<?php

namespace Backend\Database\Seeds;

use Mini\Database\ORM\Model;
use Mini\Database\Seeder;


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
        //$this->call('Backend\Database\Seeds\FoobarTableSeeder');
        $this->call('Backend\Database\Seeds\UsersTableSeeder');
        $this->call('Backend\Database\Seeds\RolesTableSeeder');
    }

}
