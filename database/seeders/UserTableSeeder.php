<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('User')->insert([
            [
                'id_user' => Str::uuid(), 
                'role_id' => 'own', 
                'email' => 'owner@gmail.com', 
                'password' => Hash::make('ownerowner'), 
                'fullname' => 'Owner', 
                'phone_number' => '08123456789', 
                'is_verified' => '1'
            ],
            [
                'id_user' => Str::uuid(), 
                'role_id' => 'adm', 
                'email' => 'admin@gmail.com', 
                'password' => Hash::make('adminadmin'), 
                'fullname' => 'Admin 1', 
                'phone_number' => '08123456789', 
                'is_verified' => '1'
            ],
            [
                'id_user' => Str::uuid(), 
                'role_id' => 'adm', 
                'email' => 'admin2@gmail.com', 
                'password' => Hash::make('adminadmin'), 
                'fullname' => 'Admin 2', 
                'phone_number' => '08123456789', 
                'is_verified' => '1'
            ],
            [
                'id_user' => Str::uuid(), 
                'role_id' => 'adm', 
                'email' => 'admin3@gmail.com', 
                'password' => Hash::make('adminadmin'), 
                'fullname' => 'Admin 3', 
                'phone_number' => '08123456789', 
                'is_verified' => '1'
            ],
            [
                'id_user' => Str::uuid(), 
                'role_id' => 'adm', 
                'email' => 'admin4@gmail.com', 
                'password' => Hash::make('adminadmin'), 
                'fullname' => 'Admin 4', 
                'phone_number' => '08123456789', 
                'is_verified' => '1'
            ],
            [
                'id_user' => Str::uuid(), 
                'role_id' => 'adm', 
                'email' => 'admin5@gmail.com', 
                'password' => Hash::make('adminadmin'), 
                'fullname' => 'Admin 5', 
                'phone_number' => '08123456789', 
                'is_verified' => '1'
            ],
            [
                'id_user' => Str::uuid(), 
                'role_id' => 'cust', 
                'email' => 'customer@gmail.com', 
                'password' => Hash::make('customercustomer'), 
                'fullname' => 'Customer 1', 
                'phone_number' => '08123456789', 
                'is_verified' => '1'
            ],
            [
                'id_user' => Str::uuid(), 
                'role_id' => 'cust', 
                'email' => 'customer2@gmail.com', 
                'password' => Hash::make('customercustomer'), 
                'fullname' => 'Customer 2', 
                'phone_number' => '08123456789', 
                'is_verified' => '1'
            ],
        ]);
    }
}
