<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get role IDs
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $landlordRole = DB::table('roles')->where('name', 'landlord')->first();
        $applicantLandlordRole = DB::table('roles')->where('name', 'applicant_landlord')->first();
        $tenantRole = DB::table('roles')->where('name', 'tenant')->first();
        $applicantTenantRole = DB::table('roles')->where('name', 'applicant_tenant')->first();

        // Get all permissions
        $allPermissions = DB::table('permissions')->pluck('id', 'name');

        // Admin gets all permissions
        foreach ($allPermissions as $permissionId) {
            DB::table('role_permission')->insert([
                'role_id' => $adminRole->id,
                'permission_id' => $permissionId,
            ]);
        }

        // Landlord permissions
        $landlordPermissions = [
            'create-property', 'edit-property', 'delete-property',
            'manage-property-applications', 'view-landlord-dashboard',
            'manage-tenants', 'view-analytics',
        ];
        foreach ($landlordPermissions as $permName) {
            if (isset($allPermissions[$permName])) {
                DB::table('role_permission')->insert([
                    'role_id' => $landlordRole->id,
                    'permission_id' => $allPermissions[$permName],
                ]);
            }
        }

        // Applicant Landlord permissions
        $applicantLandlordPermissions = [
            'view-own-landlord-application',
        ];
        foreach ($applicantLandlordPermissions as $permName) {
            if (isset($allPermissions[$permName])) {
                DB::table('role_permission')->insert([
                    'role_id' => $applicantLandlordRole->id,
                    'permission_id' => $allPermissions[$permName],
                ]);
            }
        }

        // Tenant permissions
        $tenantPermissions = [
            'apply-for-rental', 'view-own-applications', 'submit-landlord-application',
            'view-own-rentals', 'pay-rent', 'submit-maintenance-request',
        ];
        foreach ($tenantPermissions as $permName) {
            if (isset($allPermissions[$permName])) {
                DB::table('role_permission')->insert([
                    'role_id' => $tenantRole->id,
                    'permission_id' => $allPermissions[$permName],
                ]);
            }
        }

        // Applicant Tenant permissions
        $applicantTenantPermissions = [
            'view-own-applications',
        ];
        foreach ($applicantTenantPermissions as $permName) {
            if (isset($allPermissions[$permName])) {
                DB::table('role_permission')->insert([
                    'role_id' => $applicantTenantRole->id,
                    'permission_id' => $allPermissions[$permName],
                ]);
            }
        }
    }
}
