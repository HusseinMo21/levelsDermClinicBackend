<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\InventoryItem;
use App\Models\Supplier;
use App\Models\Lead;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ExampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating example data for Levels Derm Clinic...');

        // Create users for all 6 roles
        $this->createUsers();
        
        // Create patients
        $this->createPatients();
        
        // Create doctors
        $this->createDoctors();
        
        // Create services
        $this->createServices();
        
        // Create appointments
        $this->createAppointments();
        
        // Create payments
        $this->createPayments();
        
        // Create inventory items
        $this->createInventoryItems();
        
        // Create suppliers
        $this->createSuppliers();
        
        // Create leads
        $this->createLeads();

        $this->command->info('Example data created successfully!');
        $this->displayLoginCredentials();
    }

    private function createUsers()
    {
        $this->command->info('Creating users for all roles...');

        // Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@levelsderm.com'],
            [
                'name' => 'أحمد صالح - مدير النظام',
                'email' => 'admin@levelsderm.com',
                'password' => Hash::make('password123'),
                'phone' => '0501234567',
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        // Receptionist User
        $receptionist = User::firstOrCreate(
            ['email' => 'receptionist@levelsderm.com'],
            [
                'name' => 'فاطمة محمد - موظف استقبال',
                'email' => 'receptionist@levelsderm.com',
                'password' => Hash::make('password123'),
                'phone' => '0501234568',
                'is_active' => true,
            ]
        );
        $receptionist->assignRole('receptionist');

        // Doctor User
        $doctor = User::firstOrCreate(
            ['email' => 'doctor@levelsderm.com'],
            [
                'name' => 'د. سارة أحمد - طبيبة جلدية',
                'email' => 'doctor@levelsderm.com',
                'password' => Hash::make('password123'),
                'phone' => '0501234569',
                'is_active' => true,
            ]
        );
        $doctor->assignRole('doctor');

        // Inventory Manager User
        $inventory = User::firstOrCreate(
            ['email' => 'inventory@levelsderm.com'],
            [
                'name' => 'خالد علي - مدير المخزن',
                'email' => 'inventory@levelsderm.com',
                'password' => Hash::make('password123'),
                'phone' => '0501234570',
                'is_active' => true,
            ]
        );
        $inventory->assignRole('inventory');

        // Customer Service User
        $customerService = User::firstOrCreate(
            ['email' => 'customerservice@levelsderm.com'],
            [
                'name' => 'نورا حسن - خدمة العملاء',
                'email' => 'customerservice@levelsderm.com',
                'password' => Hash::make('password123'),
                'phone' => '0501234571',
                'is_active' => true,
            ]
        );
        $customerService->assignRole('customerservice');

        // Patient User
        $patient = User::firstOrCreate(
            ['email' => 'patient@levelsderm.com'],
            [
                'name' => 'مريم عبدالله - مريضة',
                'email' => 'patient@levelsderm.com',
                'password' => Hash::make('password123'),
                'phone' => '0501234572',
                'is_active' => true,
            ]
        );
        $patient->assignRole('patient');
    }
