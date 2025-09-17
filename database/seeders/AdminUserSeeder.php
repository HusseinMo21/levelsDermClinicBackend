<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@levelsderm.com'],
            [
                'name' => 'مدير النظام',
                'email' => 'admin@levelsderm.com',
                'password' => Hash::make('password123'),
                'phone' => '0501234567',
                'is_active' => true,
            ]
        );

        // Assign admin role
        $admin->assignRole('admin');

        // Create receptionist user
        $receptionist = User::firstOrCreate(
            ['email' => 'receptionist@levelsderm.com'],
            [
                'name' => 'أحمد محمد - استقبال',
                'email' => 'receptionist@levelsderm.com',
                'password' => Hash::make('password123'),
                'phone' => '0501234568',
                'is_active' => true,
            ]
        );

        $receptionist->assignRole('receptionist');

        // Create doctor user
        $doctor = User::firstOrCreate(
            ['email' => 'doctor@levelsderm.com'],
            [
                'name' => 'د. سارة أحمد',
                'email' => 'doctor@levelsderm.com',
                'password' => Hash::make('password123'),
                'phone' => '0501234569',
                'is_active' => true,
            ]
        );

        $doctor->assignRole('doctor');

        $this->command->info('Default users created successfully!');
        $this->command->info('Admin: admin@levelsderm.com / password123');
        $this->command->info('Receptionist: receptionist@levelsderm.com / password123');
        $this->command->info('Doctor: doctor@levelsderm.com / password123');
    }
}