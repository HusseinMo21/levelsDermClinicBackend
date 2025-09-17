<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for beauty clinic operations
        $permissions = [
            // User Management
            'view-users', 'create-users', 'edit-users', 'delete-users',
            
            // Patient Management
            'view-patients', 'create-patients', 'edit-patients', 'delete-patients',
            'view-patient-history', 'view-patient-medical-records',
            
            // Appointment Management
            'view-appointments', 'create-appointments', 'edit-appointments', 'delete-appointments',
            'manage-appointment-schedule', 'view-appointment-calendar',
            
            // Service Management
            'view-services', 'create-services', 'edit-services', 'delete-services',
            'manage-service-packages', 'view-service-pricing',
            
            // Inventory Management
            'view-inventory', 'create-inventory', 'edit-inventory', 'delete-inventory',
            'manage-stock', 'view-inventory-reports', 'manage-suppliers',
            
            // Payment Management
            'view-payments', 'create-payments', 'edit-payments', 'delete-payments',
            'process-refunds', 'view-payment-reports', 'manage-invoices',
            
            // Staff Management
            'view-staff', 'create-staff', 'edit-staff', 'delete-staff',
            'manage-staff-schedule', 'view-staff-performance',
            
            // Reports & Analytics
            'view-reports', 'export-reports', 'view-analytics', 'view-financial-reports',
            
            // System Administration
            'manage-roles', 'manage-permissions', 'system-settings', 'backup-data',
            
            // Customer Service
            'handle-complaints', 'manage-feedback', 'customer-support',
            
            // Medical/Doctor specific
            'view-medical-records', 'create-medical-records', 'edit-medical-records',
            'prescribe-treatments', 'view-treatment-history', 'manage-prescriptions',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles with specific permissions
        $this->createAdminRole();
        $this->createCustomerServiceRole();
        $this->createReceptionistRole();
        $this->createInventoryRole();
        $this->createDoctorRole();
        $this->createPatientRole();
    }

    private function createAdminRole()
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
    }

    private function createCustomerServiceRole()
    {
        $csRole = Role::firstOrCreate(['name' => 'customerservice']);
        $csRole->givePermissionTo([
            'view-patients', 'create-patients', 'edit-patients',
            'view-appointments', 'create-appointments', 'edit-appointments',
            'view-services', 'view-service-pricing',
            'view-payments', 'create-payments',
            'handle-complaints', 'manage-feedback', 'customer-support',
            'view-reports', 'export-reports',
        ]);
    }

    private function createReceptionistRole()
    {
        $receptionistRole = Role::firstOrCreate(['name' => 'receptionist']);
        $receptionistRole->givePermissionTo([
            'view-patients', 'create-patients', 'edit-patients',
            'view-appointments', 'create-appointments', 'edit-appointments', 'delete-appointments',
            'manage-appointment-schedule', 'view-appointment-calendar',
            'view-services', 'view-service-pricing',
            'view-payments', 'create-payments', 'edit-payments',
            'view-staff', 'manage-staff-schedule',
            'view-reports',
        ]);
    }

    private function createInventoryRole()
    {
        $inventoryRole = Role::firstOrCreate(['name' => 'inventory']);
        $inventoryRole->givePermissionTo([
            'view-inventory', 'create-inventory', 'edit-inventory', 'delete-inventory',
            'manage-stock', 'view-inventory-reports', 'manage-suppliers',
            'view-services', 'edit-services',
            'view-reports', 'export-reports',
        ]);
    }

    private function createDoctorRole()
    {
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $doctorRole->givePermissionTo([
            'view-patients', 'edit-patients', 'view-patient-history', 'view-patient-medical-records',
            'view-appointments', 'edit-appointments', 'view-appointment-calendar',
            'view-services', 'view-service-pricing',
            'view-medical-records', 'create-medical-records', 'edit-medical-records',
            'prescribe-treatments', 'view-treatment-history', 'manage-prescriptions',
            'view-payments',
            'view-reports',
        ]);
    }

    private function createPatientRole()
    {
        $patientRole = Role::firstOrCreate(['name' => 'patient']);
        $patientRole->givePermissionTo([
            'view-patients', 'edit-patients', // Only their own profile
            'view-appointments', 'create-appointments', 'edit-appointments', // Only their own
            'view-services', 'view-service-pricing',
            'view-payments', // Only their own payments
            'manage-feedback', // Can give feedback
            'view-medical-records', // Only their own records
        ]);
    }
}
