<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('Role')->insert([
            ['id_role' => 'adm', 'name' => 'ADMIN'],
            ['id_role' => 'all', 'name' => 'ALL'],
            ['id_role' => 'cust', 'name' => 'CUSTOMER'],
            ['id_role' => 'own', 'name' => 'OWNER'],
        ]);
    }
}
