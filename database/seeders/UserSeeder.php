<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@sales.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        $admin->syncRoles('admin');

        $staff = User::firstOrCreate(
            ['email' => 'staff@sales.com'],
            ['name' => 'Staff', 'password' => bcrypt('password')]
        );
        $staff->syncRoles('staff');
    }
}
