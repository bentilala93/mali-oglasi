<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin12'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        $customer1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@gmail.com',
            'password' => bcrypt('user12'),
            'email_verified_at' => now(),
        ]);
        $customer1->assignRole('customer');

        $customer2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@gmail.com',
            'password' => bcrypt('user23'),
            'email_verified_at' => now(),
        ]);
        $customer2->assignRole('customer');
    }
}