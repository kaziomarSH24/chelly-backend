<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles using firstOrCreate to prevent duplicate errors
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // Create or retrieve the admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'John Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('11111111'),
                'phone' => '+880123456789',
                'address' => 'Dhaka, Bangladesh',
                'status' => 'active',
            ]
        );

        // Sync role safely to prevent duplicate relationship entries
        $adminUser->syncRoles([$adminRole]);

        // Create or retrieve the customer user
        $customerUser = User::firstOrCreate(
            ['email' => 'kaziomar@yopmail.com'],
            [
                'name' => 'John Customer',
                'password' => Hash::make('11111111'),
                'email_verified_at' => now(),
                'phone' => '+8801711223344',
                'address' => 'Dhaka, Bangladesh',
                'status' => 'active',
            ]
        );

        // Sync role safely
        $customerUser->syncRoles([$customerRole]);
    }
}
