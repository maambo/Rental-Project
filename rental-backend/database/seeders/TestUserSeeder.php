<?php

namespace Database\Seeders;

use App\Models\LandlordApplication;
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

        $landlord = User::updateOrCreate(
            ['email' => 'landlord@rentalapp.com'],
            [
                'name'     => 'Test Landlord',
                'password' => Hash::make('password'),
                'role_id'  => $landlordRole?->id,
            ]
        );

        // Create an approved landlord application so the landlord can manage properties
        LandlordApplication::updateOrCreate(
            ['user_id' => $landlord->id],
            [
                'nrc_passport' => 'TEST123456',
                'address'      => 'Plot 1, Test Road, Lusaka',
                'province'     => 'Lusaka',
                'town'         => 'Lusaka',
                'landlord_type'    => 'private_landlord',
                'status'           => 'approved',
                'id_document_url'      => 'test-placeholder.jpg',
                'proof_of_address_url' => 'test-placeholder.jpg',
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
