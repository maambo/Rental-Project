<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $landlordRole = Role::where('name', 'landlord')->first();
        $tenantRole   = Role::where('name', 'tenant')->first();

        User::updateOrCreate(
            ['email' => 'landlord@rentalapp.com'],
            [
                'name'     => 'Test Landlord',
                'password' => Hash::make('password'),
                'role_id'  => $landlordRole?->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'tenant@rentalapp.com'],
            [
                'name'     => 'Test Tenant',
                'password' => Hash::make('password'),
                'role_id'  => $tenantRole?->id,
            ]
        );
    }
}
