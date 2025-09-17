<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Appointment;
use App\Http\Requests\StoreDoctorRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 */
class DoctorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Doctor::with(['user', 'appointments']);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
                });
            }

            // Specialization filter
            if ($request->filled('specialization')) {
                $query->where('specialization', $request->specialization);
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 12);
            $doctors = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Format data to match dashboard interface
            $doctorsData = $doctors->map(function ($doctor) {
                // Map status to Arabic
                $statusMap = [
                    'active' => 'نشط',
                    'inactive' => 'غير نشط',
                    'suspended' => 'معلق',
                ];

                // Count today's appointments
                $todayAppointments = $doctor->appointments()
                    ->whereDate('appointment_date', Carbon::today())
                    ->count();

                return [
                    'id' => $doctor->id,
                    'doctor_name' => $doctor->full_name,
                    'specialization' => $doctor->specialization,
                    'today_appointments' => $todayAppointments,
                    'status' => $statusMap[$doctor->status] ?? $doctor->status,
                    'consultation_fee' => $doctor->consultation_fee,
                    'experience_years' => $doctor->experience_years,
                    'license_number' => $doctor->license_number,
                    'qualifications' => $doctor->qualifications,
                    'bio' => $doctor->bio,
                    'working_hours' => $doctor->working_hours,
                    'available_days' => $doctor->available_days,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب قائمة الأطباء بنجاح',
                'data' => $doctorsData,
                'pagination' => [
                    'current_page' => $doctors->currentPage(),
                    'last_page' => $doctors->lastPage(),
                    'per_page' => $doctors->perPage(),
                    'total' => $doctors->total(),
                    'from' => $doctors->firstItem(),
                    'to' => $doctors->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب قائمة الأطباء',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $doctor = Doctor::with(['user', 'appointments'])
                ->findOrFail($id);

            // Map status to Arabic
            $statusMap = [
                'active' => 'نشط',
                'inactive' => 'غير نشط',
                'suspended' => 'معلق',
            ];

            // Count appointments
            $todayAppointments = $doctor->appointments()
                ->whereDate('appointment_date', Carbon::today())
                ->count();

            $totalAppointments = $doctor->appointments()->count();

            $doctorDetails = [
                'id' => $doctor->id,
                'doctor_name' => $doctor->full_name,
                'specialization' => $doctor->specialization,
                'license_number' => $doctor->license_number,
                'qualifications' => $doctor->qualifications,
                'experience_years' => $doctor->experience_years,
                'consultation_fee' => $doctor->consultation_fee,
                'bio' => $doctor->bio,
                'status' => $statusMap[$doctor->status] ?? $doctor->status,
                'working_hours' => $doctor->working_hours,
                'available_days' => $doctor->available_days,
                'today_appointments' => $todayAppointments,
                'total_appointments' => $totalAppointments,
                'notes' => $doctor->notes ?? '',
                'profile_image' => $doctor->profile_image,
                'created_at' => $doctor->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $doctor->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'تم جلب تفاصيل الطبيب بنجاح',
                'data' => $doctorDetails
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب تفاصيل الطبيب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(string $term): JsonResponse
    {
        try {
            $doctors = Doctor::with(['user'])
                ->where(function ($query) use ($term) {
                    $query->whereHas('user', function ($userQuery) use ($term) {
                        $userQuery->where('name', 'like', "%{$term}%");
                    })
                    ->orWhere('specialization', 'like', "%{$term}%")
                    ->orWhere('license_number', 'like', "%{$term}%");
                })
                ->where('status', 'active')
                ->limit(10)
                ->get();

            $doctorsData = $doctors->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'doctor_name' => $doctor->full_name,
                    'specialization' => $doctor->specialization,
                    'license_number' => $doctor->license_number,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم البحث في الأطباء بنجاح',
                'data' => $doctorsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء البحث في الأطباء',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getBySpecialization(string $specialization): JsonResponse
    {
        try {
            $doctors = Doctor::with(['user'])
                ->where('specialization', $specialization)
                ->where('status', 'active')
                ->get();

            $doctorsData = $doctors->map(function ($doctor) {
                $todayAppointments = $doctor->appointments()
                    ->whereDate('appointment_date', Carbon::today())
                    ->count();

                return [
                    'id' => $doctor->id,
                    'doctor_name' => $doctor->full_name,
                    'specialization' => $doctor->specialization,
                    'today_appointments' => $todayAppointments,
                    'consultation_fee' => $doctor->consultation_fee,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب الأطباء حسب التخصص بنجاح',
                'data' => $doctorsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الأطباء حسب التخصص',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSpecializations(): JsonResponse
    {
        try {
            $specializations = Doctor::where('status', 'active')
                ->distinct()
                ->pluck('specialization')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'تم جلب التخصصات بنجاح',
                'data' => $specializations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب التخصصات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     *     path="/api/doctors/{id}/info",
     *     summary="Get doctor comprehensive info",
     *     description="Get comprehensive doctor information including statistics, reservations, and tools used",
     *     tags={"Doctors"},
     *     security={{"bearerAuth":{}}},
     *         name="id",
     *         in="path",
     *         description="Doctor ID",
     *         required=true,
     *     ),
     *         response=200,
     *         description="Successful operation",
     *                 property="data",
     *                 type="object",
     *                     property="doctor_info",
     *                     type="object",
     *                 ),
     *                     property="reservation_statistics",
     *                     type="object",
     *                 ),
     *                     property="reservations_table",
     *                     type="array",
     *                     )
     *                 ),
     *                     property="tools_table",
     *                     type="array",
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getDoctorInfo(int $id): JsonResponse
    {
        try {
            $doctor = Doctor::with(['user', 'appointments.patient', 'appointments.service'])
                ->findOrFail($id);

            // Doctor basic info
            $doctorInfo = [
                'id' => $doctor->id,
                'doctor_name' => $doctor->full_name,
                'specialization' => $doctor->specialization,
                'license_number' => $doctor->license_number,
                'consultation_fee' => $doctor->consultation_fee,
            ];

            // Reservation Statistics
            $today = Carbon::today();
            $totalToday = $doctor->appointments()
                ->whereDate('appointment_date', $today)
                ->count();

            $reservationsDone = $doctor->appointments()
                ->whereDate('appointment_date', $today)
                ->where('status', 'completed')
                ->count();

            $pendingReservations = $doctor->appointments()
                ->whereDate('appointment_date', $today)
                ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
                ->count();

            $reservationsLeft = $totalToday - $reservationsDone;

            $reservationStatistics = [
                'total_today' => $totalToday,
                'reservations_done' => $reservationsDone,
                'pending_reservations' => $pendingReservations,
                'reservations_left' => $reservationsLeft,
            ];

            // Reservations Table
            $reservations = $doctor->appointments()
                ->with(['patient', 'service'])
                ->whereDate('appointment_date', $today)
                ->orderBy('appointment_date', 'asc')
                ->get();

            // Map status to Arabic
            $statusMap = [
                'scheduled' => 'تم الحجز',
                'confirmed' => 'تم الحجز',
                'in_progress' => 'قيد التنفيذ',
                'completed' => 'تم التنفيذ',
                'cancelled' => 'ألغي',
                'no_show' => 'لم يحضر',
                'postponed' => 'تأجيل',
            ];

            $reservationsTable = $reservations->map(function ($appointment) use ($statusMap) {
                return [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->patient->full_name ?? 'غير محدد',
                    'time' => $appointment->appointment_date->format('H:i'),
                    'service_name' => $appointment->service->name ?? 'غير محدد',
                    'condition' => $statusMap[$appointment->status] ?? $appointment->status,
                    'notes' => $appointment->notes ?? '',
                ];
            });

            // Tools Table (Mock data for now - you can implement actual tools tracking later)
            $toolsTable = [
                [
                    'id' => 1,
                    'tool_name' => 'مشرط جراحي',
                    'quantity' => 2,
                    'date' => $today->format('Y-m-d'),
                    'notes' => 'لعملية تجميلية',
                ],
                [
                    'id' => 2,
                    'tool_name' => 'خيوط جراحية',
                    'quantity' => 5,
                    'date' => $today->format('Y-m-d'),
                    'notes' => 'لإغلاق الجروح',
                ],
                [
                    'id' => 3,
                    'tool_name' => 'مطهر طبي',
                    'quantity' => 1,
                    'date' => $today->format('Y-m-d'),
                    'notes' => 'لتعقيم المنطقة',
                ],
            ];

            $doctorInfoData = [
                'doctor_info' => $doctorInfo,
                'reservation_statistics' => $reservationStatistics,
                'reservations_table' => $reservationsTable,
                'tools_table' => $toolsTable,
            ];

            return response()->json([
                'success' => true,
                'message' => 'تم جلب معلومات الطبيب بنجاح',
                'data' => $doctorInfoData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب معلومات الطبيب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     *     path="/api/doctors",
     *     summary="Create a new doctor",
     *     description="Create a new doctor with all required information including basic info, role, permissions, financial info, working days, and credentials",
     *     tags={"Doctors"},
     *     security={{"bearerAuth":{}}},
     *         required=true,
     *             required={"first_name","second_name","third_name","fourth_name","phone","national_id","email","address","role","permissions","monthly_salary","detection_value","doctor_percentage","working_days","username","password","password_confirmation","license_number","specialization","consultation_fee"},
     *                 property="permissions",
     *                 type="array",
     *             ),
     *                 property="working_days",
     *                 type="array",
     *                 )
     *             ),
     *         )
     *     ),
     *         response=201,
     *         description="Doctor created successfully",
     *                 property="data",
     *                 type="object",
     *             )
     *         )
     *     ),
     *         response=422,
     *         description="Validation error",
     *         )
     *     )
     * )
     */
    public function store(StoreDoctorRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create full name
            $fullName = trim($request->first_name . ' ' . $request->second_name . ' ' . $request->third_name . ' ' . $request->fourth_name);
            $displayName = 'د. ' . $fullName;

            // Create User
            $user = User::create([
                'name' => $request->username,
                'display_name' => $displayName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Assign doctor role
            $user->assignRole('doctor');

            // Assign permissions
            if ($request->has('permissions')) {
                $user->givePermissionTo($request->permissions);
            }

            // Prepare working hours and available days
            $workingHours = [];
            $availableDays = [];
            
            foreach ($request->working_days as $day) {
                if ($day['is_working']) {
                    $availableDays[] = $day['day'];
                    $workingHours[$day['day']] = [
                        'from' => $day['from_time'],
                        'to' => $day['to_time']
                    ];
                }
            }

            // Create Doctor
            $doctor = Doctor::create([
                'user_id' => $user->id,
                'doctor_id' => 'DOC' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                'license_number' => $request->license_number,
                'specialization' => $request->specialization,
                'qualifications' => $request->qualifications,
                'experience_years' => $request->experience_years,
                'consultation_fee' => $request->consultation_fee,
                'bio' => $request->bio,
                'working_hours' => $workingHours,
                'available_days' => $availableDays,
                'status' => $request->status ?? 'active',
                'notes' => $request->notes,
                // Financial information (you might want to store these in a separate table)
                'monthly_salary' => $request->monthly_salary,
                'detection_value' => $request->detection_value,
                'doctor_percentage' => $request->doctor_percentage,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطبيب بنجاح',
                'data' => [
                    'id' => $doctor->id,
                    'doctor_name' => $displayName,
                    'specialization' => $doctor->specialization,
                    'license_number' => $doctor->license_number,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'national_id' => $user->national_id,
                    'username' => $user->name,
                    'working_days' => $availableDays,
                    'working_hours' => $workingHours,
                    'monthly_salary' => $doctor->monthly_salary,
                    'detection_value' => $doctor->detection_value,
                    'doctor_percentage' => $doctor->doctor_percentage,
                    'permissions' => $user->getPermissionNames(),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الطبيب',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
