<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access with all permissions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'landlord',
                'display_name' => 'Landlord',
                'description' => 'Property owner with approved landlord status',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'applicant_landlord',
                'display_name' => 'Applicant Landlord',
                'description' => 'User who submitted landlord application, pending approval',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'tenant',
                'display_name' => 'Tenant',
                'description' => 'Renter with approved tenancy',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'applicant_tenant',
                'display_name' => 'Applicant Tenant',
                'description' => 'User who applied for property, pending landlord approval',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}
