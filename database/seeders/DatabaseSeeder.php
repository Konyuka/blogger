<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {

        $adminUser = User::factory()->create([
            'email' => 'michaelsaiba84@gmail.com',
            'name' => 'Admin',
            'password' => bcrypt('password')
        ]);

        $adminRole = Role::create(['name' => 'admin']);
        $adminUser->assignRole($adminRole);
    }
}
