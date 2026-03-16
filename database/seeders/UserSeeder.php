<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'manage-categories',
            'manage-products',
            'manage-tables',
            'manage-orders',
            'manage-payments',
            'view-reports',
            'manage-users',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $cashierRole = Role::create(['name' => 'cashier']);
        $kitchenRole = Role::create(['name' => 'kitchen']);

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign specific permissions to cashier
        $cashierRole->givePermissionTo([
            'manage-orders',
            'manage-payments',
            'view-reports',
        ]);

        // Assign specific permissions to kitchen
        $kitchenRole->givePermissionTo([
            'manage-orders',
        ]);

        // Create users
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@posresto.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $cashier = User::create([
            'name' => 'Cashier',
            'email' => 'cashier@posresto.com',
            'password' => Hash::make('password'),
        ]);
        $cashier->assignRole('cashier');

        $kitchen = User::create([
            'name' => 'Kitchen',
            'email' => 'kitchen@posresto.com',
            'password' => Hash::make('password'),
        ]);
        $kitchen->assignRole('kitchen');
    }
}
