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
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SimpleExampleDataSeeder extends Seeder
{
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
                'name' => 'admin',
                'display_name' => 'أحمد صالح - مدير النظام',
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
                'name' => 'receptionist',
                'display_name' => 'فاطمة محمد - موظف استقبال',
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
                'name' => 'sara.doctor',
                'display_name' => 'د. سارة أحمد - طبيبة جلدية',
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
                'name' => 'inventory.manager',
                'display_name' => 'خالد علي - مدير المخزن',
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
                'name' => 'customer.service',
                'display_name' => 'نورا حسن - خدمة العملاء',
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
                'name' => 'patient.user',
                'display_name' => 'مريم عبدالله - مريضة',
                'email' => 'patient@levelsderm.com',
                'password' => Hash::make('password123'),
                'phone' => '0501234572',
                'is_active' => true,
            ]
        );
        $patient->assignRole('patient');
    }

    private function createPatients()
    {
        $this->command->info('Creating patients...');

        $patients = [
            [
                'first_name' => 'نور',
                'last_name' => 'أحمد محمد',
                'national_id' => '1234567890',
                'email' => 'nour.ahmed@example.com',
                'phone' => '0501111111',
                'date_of_birth' => '1990-05-15',
                'gender' => 'female',
                'address' => 'الرياض، حي النرجس',
                'city' => 'الرياض',
                'state' => 'منطقة الرياض',
                'country' => 'السعودية',
                'status' => 'active',
                'visit_count' => 5,
                'loyalty_points' => 150,
                'last_loyalty_points_used' => Carbon::now()->subDays(10),
                'last_activity' => Carbon::now()->subDays(2),
                'first_visit_date' => Carbon::now()->subMonths(3),
            ],
            [
                'first_name' => 'كريم',
                'last_name' => 'علي حسن',
                'national_id' => '1234567891',
                'email' => 'karim.ali@example.com',
                'phone' => '0502222222',
                'date_of_birth' => '1985-08-22',
                'gender' => 'male',
                'address' => 'جدة، حي الزهراء',
                'city' => 'جدة',
                'state' => 'منطقة مكة المكرمة',
                'country' => 'السعودية',
                'status' => 'active',
                'visit_count' => 3,
                'loyalty_points' => 75,
                'last_loyalty_points_used' => Carbon::now()->subDays(5),
                'last_activity' => Carbon::now()->subDays(1),
                'first_visit_date' => Carbon::now()->subMonths(2),
            ],
            [
                'first_name' => 'علاء',
                'last_name' => 'علي محمود',
                'national_id' => '1234567892',
                'email' => 'alaa.ali@example.com',
                'phone' => '0503333333',
                'date_of_birth' => '1992-12-10',
                'gender' => 'male',
                'address' => 'الدمام، حي الفيصلية',
                'city' => 'الدمام',
                'state' => 'المنطقة الشرقية',
                'country' => 'السعودية',
                'status' => 'active',
                'visit_count' => 2,
                'loyalty_points' => 50,
                'last_loyalty_points_used' => null,
                'last_activity' => Carbon::now()->subDays(3),
                'first_visit_date' => Carbon::now()->subMonths(1),
            ],
            [
                'first_name' => 'سارة',
                'last_name' => 'محمد أحمد',
                'national_id' => '1234567893',
                'email' => 'sara.mohammed@example.com',
                'phone' => '0504444444',
                'date_of_birth' => '1988-03-18',
                'gender' => 'female',
                'address' => 'الرياض، حي الملز',
                'city' => 'الرياض',
                'state' => 'منطقة الرياض',
                'country' => 'السعودية',
                'status' => 'active',
                'visit_count' => 7,
                'loyalty_points' => 200,
                'last_loyalty_points_used' => Carbon::now()->subDays(2),
                'last_activity' => Carbon::now()->subHours(6),
                'first_visit_date' => Carbon::now()->subMonths(6),
            ],
            [
                'first_name' => 'عبدالله',
                'last_name' => 'سعد الدين',
                'national_id' => '1234567894',
                'email' => 'abdullah.saad@example.com',
                'phone' => '0505555555',
                'date_of_birth' => '1995-07-25',
                'gender' => 'male',
                'address' => 'الرياض، حي العليا',
                'city' => 'الرياض',
                'state' => 'منطقة الرياض',
                'country' => 'السعودية',
                'status' => 'inactive',
                'visit_count' => 1,
                'loyalty_points' => 25,
                'last_loyalty_points_used' => null,
                'last_activity' => Carbon::now()->subWeeks(2),
                'first_visit_date' => Carbon::now()->subWeeks(3),
            ],
        ];

        foreach ($patients as $index => $patientData) {
            Patient::firstOrCreate(
                ['national_id' => $patientData['national_id']],
                array_merge($patientData, [
                    'patient_id' => 'PAT' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                    'created_by' => User::where('email', 'receptionist@levelsderm.com')->first()->id,
                ])
            );
        }
    }

    private function createDoctors()
    {
        $this->command->info('Creating doctors...');

        $doctors = [
            [
                'user_id' => User::where('email', 'doctor@levelsderm.com')->first()->id,
                'doctor_id' => 'DOC001',
                'license_number' => 'MED123456',
                'specialization' => 'جلدية وتجميل',
                'qualifications' => 'دكتوراه في الأمراض الجلدية',
                'experience_years' => 8,
                'consultation_fee' => 300.00,
                'bio' => 'متخصصة في علاج الأمراض الجلدية والعمليات التجميلية',
                'working_hours' => '09:00-17:00',
                'available_days' => 'السبت,الأحد,الاثنين,الثلاثاء,الأربعاء',
                'status' => 'active',
            ],
            [
                'user_id' => User::where('email', 'admin@levelsderm.com')->first()->id,
                'doctor_id' => 'DOC002',
                'license_number' => 'MED123457',
                'specialization' => 'جلدية',
                'qualifications' => 'ماجستير في الأمراض الجلدية',
                'experience_years' => 5,
                'consultation_fee' => 250.00,
                'bio' => 'متخصص في علاج الأمراض الجلدية',
                'working_hours' => '10:00-18:00',
                'available_days' => 'السبت,الأحد,الاثنين,الثلاثاء,الأربعاء,الخميس',
                'status' => 'active',
            ],
        ];

        foreach ($doctors as $doctorData) {
            Doctor::firstOrCreate(
                ['doctor_id' => $doctorData['doctor_id']],
                $doctorData
            );
        }
    }

    private function createServices()
    {
        $this->command->info('Creating services...');

        $services = [
            [
                'service_code' => 'SVC001',
                'name' => 'استشارة جلدية',
                'description' => 'استشارة طبية شاملة للأمراض الجلدية',
                'category' => 'استشارات',
                'subcategory' => 'جلدية',
                'specialization' => 'جلدية',
                'price' => 200.00,
                'duration_minutes' => 30,
                'is_active' => true,
            ],
            [
                'service_code' => 'SVC002',
                'name' => 'حقن البوتوكس',
                'description' => 'حقن البوتوكس لتجديد البشرة',
                'category' => 'تجميل',
                'subcategory' => 'حقن',
                'specialization' => 'تجميل',
                'price' => 800.00,
                'duration_minutes' => 45,
                'is_active' => true,
            ],
            [
                'service_code' => 'SVC003',
                'name' => 'ليزر إزالة الشعر',
                'description' => 'جلسة ليزر لإزالة الشعر',
                'category' => 'تجميل',
                'subcategory' => 'ليزر',
                'specialization' => 'تجميل',
                'price' => 300.00,
                'duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'service_code' => 'SVC004',
                'name' => 'تنظيف البشرة',
                'description' => 'جلسة تنظيف عميق للبشرة',
                'category' => 'عناية',
                'subcategory' => 'تنظيف',
                'specialization' => 'عناية',
                'price' => 150.00,
                'duration_minutes' => 90,
                'is_active' => true,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::firstOrCreate(
                ['service_code' => $serviceData['service_code']],
                array_merge($serviceData, [
                    'created_by' => User::where('email', 'admin@levelsderm.com')->first()->id,
                ])
            );
        }
    }

    private function createAppointments()
    {
        $this->command->info('Creating appointments...');

        $today = Carbon::today();
        $patients = Patient::all();
        $doctors = Doctor::all();
        $services = Service::all();

        $appointments = [
            [
                'patient_id' => $patients[0]->id,
                'doctor_id' => $doctors[0]->id,
                'service_id' => $services[0]->id,
                'appointment_date' => $today->copy()->setTime(12, 0),
                'end_time' => $today->copy()->setTime(12, 30),
                'status' => 'completed',
                'type' => 'consultation',
                'total_amount' => 200.00,
                'discount_amount' => 0.00,
                'payment_required' => true,
                'created_by' => User::where('email', 'receptionist@levelsderm.com')->first()->id,
            ],
            [
                'patient_id' => $patients[1]->id,
                'doctor_id' => $doctors[0]->id,
                'service_id' => $services[1]->id,
                'appointment_date' => $today->copy()->setTime(14, 0),
                'end_time' => $today->copy()->setTime(14, 45),
                'status' => 'confirmed',
                'type' => 'treatment',
                'total_amount' => 800.00,
                'discount_amount' => 50.00,
                'payment_required' => true,
                'created_by' => User::where('email', 'receptionist@levelsderm.com')->first()->id,
            ],
            [
                'patient_id' => $patients[2]->id,
                'doctor_id' => $doctors[1]->id,
                'service_id' => $services[2]->id,
                'appointment_date' => $today->copy()->setTime(15, 0),
                'end_time' => $today->copy()->setTime(16, 0),
                'status' => 'confirmed',
                'type' => 'treatment',
                'total_amount' => 300.00,
                'discount_amount' => 0.00,
                'payment_required' => true,
                'created_by' => User::where('email', 'receptionist@levelsderm.com')->first()->id,
            ],
            [
                'patient_id' => $patients[3]->id,
                'doctor_id' => $doctors[0]->id,
                'service_id' => $services[0]->id,
                'appointment_date' => $today->copy()->setTime(16, 0),
                'end_time' => $today->copy()->setTime(16, 30),
                'status' => 'cancelled',
                'type' => 'consultation',
                'total_amount' => 200.00,
                'discount_amount' => 0.00,
                'payment_required' => true,
                'cancellation_reason' => 'إلغاء من قبل المريض',
                'created_by' => User::where('email', 'receptionist@levelsderm.com')->first()->id,
            ],
        ];

        foreach ($appointments as $index => $appointmentData) {
            Appointment::create(array_merge($appointmentData, [
                'appointment_id' => 'APT' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'operation_number' => 'OPR' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
            ]));
        }
    }

    private function createPayments()
    {
        $this->command->info('Creating payments...');

        $today = Carbon::today();
        $appointments = Appointment::all();

        $payments = [
            [
                'patient_id' => $appointments[0]->patient_id,
                'appointment_id' => $appointments[0]->id,
                'amount' => 200.00,
                'discount_amount' => 0.00,
                'tax_amount' => 30.00,
                'total_amount' => 230.00,
                'payment_method' => 'cash',
                'payment_source' => 'clinic',
                'status' => 'completed',
                'payment_date' => $today,
                'processed_by' => User::where('email', 'receptionist@levelsderm.com')->first()->id,
            ],
            [
                'patient_id' => $appointments[1]->patient_id,
                'appointment_id' => $appointments[1]->id,
                'amount' => 750.00,
                'discount_amount' => 50.00,
                'tax_amount' => 112.50,
                'total_amount' => 812.50,
                'payment_method' => 'card',
                'payment_source' => 'clinic',
                'status' => 'completed',
                'payment_date' => $today,
                'processed_by' => User::where('email', 'receptionist@levelsderm.com')->first()->id,
            ],
            [
                'patient_id' => $appointments[2]->patient_id,
                'appointment_id' => $appointments[2]->id,
                'amount' => 300.00,
                'discount_amount' => 0.00,
                'tax_amount' => 45.00,
                'total_amount' => 345.00,
                'payment_method' => 'card',
                'payment_source' => 'clinic',
                'status' => 'completed',
                'payment_date' => $today,
                'processed_by' => User::where('email', 'receptionist@levelsderm.com')->first()->id,
            ],
        ];

        foreach ($payments as $index => $paymentData) {
            Payment::create(array_merge($paymentData, [
                'payment_id' => 'PAY' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
            ]));
        }
    }

    private function displayLoginCredentials()
    {
        $this->command->info('');
        $this->command->info('==========================================');
        $this->command->info('🔐 LOGIN CREDENTIALS FOR ALL 6 ROLES');
        $this->command->info('==========================================');
        $this->command->info('');
        
        $credentials = [
            ['role' => 'Admin', 'email' => 'admin@levelsderm.com', 'password' => 'password123'],
            ['role' => 'Receptionist', 'email' => 'receptionist@levelsderm.com', 'password' => 'password123'],
            ['role' => 'Doctor', 'email' => 'doctor@levelsderm.com', 'password' => 'password123'],
            ['role' => 'Inventory Manager', 'email' => 'inventory@levelsderm.com', 'password' => 'password123'],
            ['role' => 'Customer Service', 'email' => 'customerservice@levelsderm.com', 'password' => 'password123'],
            ['role' => 'Patient', 'email' => 'patient@levelsderm.com', 'password' => 'password123'],
        ];

        foreach ($credentials as $cred) {
            $this->command->info("👤 {$cred['role']}:");
            $this->command->info("   📧 Email: {$cred['email']}");
            $this->command->info("   🔑 Password: {$cred['password']}");
            $this->command->info('');
        }

        $this->command->info('==========================================');
        $this->command->info('📊 EXAMPLE DATA CREATED:');
        $this->command->info('   • 5 Patients with Arabic names');
        $this->command->info('   • 2 Doctors with specializations');
        $this->command->info('   • 4 Services (consultation, botox, laser, facial)');
        $this->command->info('   • 4 Appointments for today');
        $this->command->info('   • 3 Payments with different methods');
        $this->command->info('==========================================');
    }
}
