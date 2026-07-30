<?php

namespace Database\Seeders;

use App\Models\LandlordApplication;
use App\Models\Role;
use App\Models\Town;
use App\Models\TradeCategory;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerService;
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

        // Worker — needs a verified + active profile so the marketplace listing,
        // the worker detail page and the whole /worker/* area all render.
        $worker = User::updateOrCreate(
            ['email' => 'worker@rentalapp.com'],
            [
                'name'     => 'Test Worker',
                'password' => Hash::make('password'),
                'role_id'  => $tenantRole?->id,
            ]
        );

        $category = TradeCategory::first();
        $town     = Town::first();

        if ($category && $town) {
            $profile = WorkerProfile::updateOrCreate(
                ['user_id' => $worker->id],
                [
                    'trade_category_id' => $category->id,
                    'town_id'           => $town->id,
                    'tagline'           => 'Reliable tradesman for hire',
                    'bio'               => 'Ten years of experience across residential and commercial jobs.',
                    'experience_years'  => 10,
                    'phone'             => '0977000111',
                    'is_verified'       => true,
                    'is_active'         => true,
                    'is_featured'       => true,
                    'rating_average'    => 0,
                    'rating_count'      => 0,
                ]
            );

            WorkerService::updateOrCreate(
                ['worker_profile_id' => $profile->id, 'service_name' => 'General Repairs'],
                [
                    'description'    => 'Standard callout for general repair work.',
                    'rate_type'      => 'hourly',
                    'base_rate'      => 150,
                    'minimum_charge' => 200,
                    'is_active'      => true,
                ]
            );
        }
    }
}
