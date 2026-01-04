<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $permissions = [
            // Property Management
            ['name' => 'create-property', 'display_name' => 'Create Property', 'description' => 'Create new properties', 'group' => 'properties'],
            ['name' => 'edit-property', 'display_name' => 'Edit Property', 'description' => 'Edit own properties', 'group' => 'properties'],
            ['name' => 'delete-property', 'display_name' => 'Delete Property', 'description' => 'Delete own properties', 'group' => 'properties'],
            ['name' => 'view-all-properties', 'display_name' => 'View All Properties', 'description' => 'View all properties (admin)', 'group' => 'properties'],
            
            // Application Management
            ['name' => 'apply-for-rental', 'display_name' => 'Apply for Rental', 'description' => 'Apply to rent properties', 'group' => 'applications'],
            ['name' => 'view-own-applications', 'display_name' => 'View Own Applications', 'description' => 'View own rental applications', 'group' => 'applications'],
            ['name' => 'manage-property-applications', 'display_name' => 'Manage Property Applications', 'description' => 'Manage applications for own properties', 'group' => 'applications'],
            ['name' => 'view-own-landlord-application', 'display_name' => 'View Own Landlord Application', 'description' => 'View status of landlord application', 'group' => 'applications'],
            
            // User Management
            ['name' => 'view-users', 'display_name' => 'View Users', 'description' => 'View user list', 'group' => 'users'],
            ['name' => 'edit-users', 'display_name' => 'Edit Users', 'description' => 'Edit user details', 'group' => 'users'],
            ['name' => 'delete-users', 'display_name' => 'Delete Users', 'description' => 'Delete users', 'group' => 'users'],
            ['name' => 'manage-roles', 'display_name' => 'Manage Roles', 'description' => 'Manage roles and permissions', 'group' => 'users'],
            
            // Landlord Features
            ['name' => 'submit-landlord-application', 'display_name' => 'Submit Landlord Application', 'description' => 'Apply to become landlord', 'group' => 'landlord'],
            ['name' => 'view-landlord-dashboard', 'display_name' => 'View Landlord Dashboard', 'description' => 'Access landlord dashboard', 'group' => 'landlord'],
            ['name' => 'manage-tenants', 'display_name' => 'Manage Tenants', 'description' => 'Manage property tenants', 'group' => 'landlord'],
            ['name' => 'view-analytics', 'display_name' => 'View Analytics', 'description' => 'View property analytics', 'group' => 'landlord'],
            ['name' => 'approve-landlord-applications', 'display_name' => 'Approve Landlord Applications', 'description' => 'Review and approve landlord applications', 'group' => 'landlord'],
            
            // Tenant Features
            ['name' => 'view-own-rentals', 'display_name' => 'View Own Rentals', 'description' => 'View rented properties', 'group' => 'tenant'],
            ['name' => 'pay-rent', 'display_name' => 'Pay Rent', 'description' => 'Make rent payments', 'group' => 'tenant'],
            ['name' => 'submit-maintenance-request', 'display_name' => 'Submit Maintenance Request', 'description' => 'Submit maintenance requests', 'group' => 'tenant'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
