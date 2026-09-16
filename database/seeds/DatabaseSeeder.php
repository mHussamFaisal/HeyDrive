<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
//use Database\Seeds\PermissionsTableSeeder;
//use Database\Seeds\RolesTableSeeder;
//use Database\Seeds\ConnectRelationshipsSeeder;

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

        // $this->call(PermissionsTableSeeder::class);
        // $this->call(RolesTableSeeder::class);
        // $this->call(ConnectRelationshipsSeeder::class);
        // $this->call(UsersTableSeeder::class);

        Model::reguard();
    }
}
