<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * @OA\Tag(name="Patients")
 */
class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/clients",
     *     summary="Get list of clients (patients)",
     *     description="Retrieve a paginated list of clients matching the dashboard interface",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for client name, phone, or national ID",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by client status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive", "suspended"})
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter by registration date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تم جلب قائمة العملاء بنجاح"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="client_name", type="string", example="أحمد حسين"),
     *                     @OA\Property(property="phone_number", type="string", example="01012345678"),
     *                     @OA\Property(property="national_id", type="string", example="22155338568"),
     *                     @OA\Property(property="date_registered", type="string", example="8 يونيو - 5:00م"),
     *                     @OA\Property(property="visit_numbers", type="string", example="3 زيارات"),
     *                     @OA\Property(property="activity", type="string", example="نشط"),
     *                     @OA\Property(property="last_visit_date", type="string", example="8 يونيو - 5:00م")
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function clients(Request $request): JsonResponse
    {
        try {
            $query = Patient::with(['appointments']);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('national_id', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Date filter
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            $perPage = $request->get('per_page', 15);
            $patients = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Format data to match dashboard interface
            $clientsData = $patients->map(function ($patient) {
                // Map status to Arabic
                $statusMap = [
                    'active' => 'نشط',
                    'inactive' => 'غير نشط',
                    'suspended' => 'معلق',
                ];

                // Format visit count
                $visitCount = $patient->visit_count;
                $visitText = $visitCount == 1 ? 'زيارة واحدة' : $visitCount . ' زيارات';

                // Get last visit date
                $lastVisit = $patient->appointments()
                    ->orderBy('appointment_date', 'desc')
                    ->first();
                
                $lastVisitDate = $lastVisit ? 
                    $lastVisit->appointment_date->format('j M - g:iA') : 
                    'لا توجد زيارات';

                return [
                    'id' => $patient->id,
                    'client_name' => $patient->full_name,
                    'phone_number' => $patient->phone,
                    'national_id' => $patient->national_id,
                    'date_registered' => $patient->created_at->format('j M - g:iA'),
                    'visit_numbers' => $visitText,
                    'activity' => $statusMap[$patient->status] ?? $patient->status,
                    'status' => $patient->status, // Keep original for backend use
                    'last_visit_date' => $lastVisitDate,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب قائمة العملاء بنجاح',
                'data' => $clientsData,
                'pagination' => [
                    'current_page' => $patients->currentPage(),
                    'last_page' => $patients->lastPage(),
                    'per_page' => $patients->perPage(),
                    'total' => $patients->total(),
                    'from' => $patients->firstItem(),
                    'to' => $patients->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب قائمة العملاء',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/clients/{id}/details",
     *     summary="Get client details",
     *     description="Get comprehensive client details including personal info, loyalty points, reservation history, and notes",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Client ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تم جلب تفاصيل العميل بنجاح"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="personal_information",
     *                     type="object",
     *                     @OA\Property(property="full_name", type="string", example="أحمد حسين محمد"),
     *                     @OA\Property(property="national_id", type="string", example="22155338568"),
     *                     @OA\Property(property="first_visit_date", type="string", example="2025-01-15"),
     *                     @OA\Property(property="phone_number", type="string", example="01012345678"),
     *                     @OA\Property(property="gender", type="string", example="ذكر"),
     *                     @OA\Property(property="last_activity", type="string", example="2025-09-17 14:30")
     *                 ),
     *                 @OA\Property(
     *                     property="loyalty_points",
     *                     type="object",
     *                     @OA\Property(property="current_points", type="integer", example=150),
     *                     @OA\Property(property="last_used", type="string", example="2025-09-10 10:00")
     *                 ),
     *                 @OA\Property(
     *                     property="reservation_history",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="date", type="string", example="2025-09-17"),
     *                         @OA\Property(property="service", type="string", example="استشارة جلدية"),
     *                         @OA\Property(property="doctor", type="string", example="د. سارة أحمد"),
     *                         @OA\Property(property="status", type="string", example="تم التنفيذ"),
     *                         @OA\Property(property="amount", type="number", example=200.00)
     *                     )
     *                 ),
     *                 @OA\Property(property="notes", type="string", example="ملاحظات إضافية حول العميل")
     *             )
     *         )
     *     )
     * )
     */
    public function clientDetails(int $id): JsonResponse
    {
        try {
            $patient = Patient::with(['appointments.doctor.user', 'appointments.service', 'payments'])
                ->findOrFail($id);

            // Map gender to Arabic
            $genderMap = [
                'male' => 'ذكر',
                'female' => 'أنثى',
            ];

            // Map status to Arabic
            $statusMap = [
                'scheduled' => 'مجدول',
                'confirmed' => 'مؤكد',
                'in_progress' => 'قيد التنفيذ',
                'completed' => 'تم التنفيذ',
                'cancelled' => 'ألغي',
                'no_show' => 'لم يحضر',
            ];

            // Personal Information
            $personalInfo = [
                'full_name' => $patient->full_name,
                'national_id' => $patient->national_id,
                'first_visit_date' => $patient->first_visit_date ? $patient->first_visit_date->format('Y-m-d') : 'غير محدد',
                'phone_number' => $patient->phone,
                'gender' => $genderMap[$patient->gender] ?? $patient->gender,
                'last_activity' => $patient->last_activity ? $patient->last_activity->format('Y-m-d H:i') : 'غير محدد',
            ];

            // Loyalty Points
            $loyaltyPoints = [
                'current_points' => $patient->loyalty_points ?? 0,
                'last_used' => $patient->last_loyalty_points_used ? $patient->last_loyalty_points_used->format('Y-m-d H:i') : 'لم يتم الاستخدام',
            ];

            // Reservation History
            $reservationHistory = $patient->appointments()
                ->orderBy('appointment_date', 'desc')
                ->get()
                ->map(function ($appointment) use ($statusMap) {
                    return [
                        'id' => $appointment->id,
                        'date' => $appointment->appointment_date->format('Y-m-d'),
                        'service' => $appointment->service->name ?? 'غير محدد',
                        'doctor' => $appointment->doctor->user->name ?? 'غير محدد',
                        'status' => $statusMap[$appointment->status] ?? $appointment->status,
                        'amount' => $appointment->total_amount,
                    ];
                });

            $clientDetails = [
                'personal_information' => $personalInfo,
                'loyalty_points' => $loyaltyPoints,
                'reservation_history' => $reservationHistory,
                'notes' => $patient->notes ?? '',
            ];

            return response()->json([
                'success' => true,
                'message' => 'تم جلب تفاصيل العميل بنجاح',
                'data' => $clientDetails
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب تفاصيل العميل',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/patients",
     *     summary="Get list of patients",
     *     description="Retrieve a paginated list of patients",
     *     tags={"Patients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Patient::with(['createdBy']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('national_id', 'like', "%{$search}%")
                      ->orWhere('patient_id', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            $perPage = $request->get('per_page', 15);
            $patients = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'تم جلب قائمة المرضى بنجاح',
                'data' => PatientResource::collection($patients->items()),
                'pagination' => [
                    'current_page' => $patients->currentPage(),
                    'per_page' => $patients->perPage(),
                    'total' => $patients->total(),
                    'last_page' => $patients->lastPage(),
                    'from' => $patients->firstItem(),
                    'to' => $patients->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب قائمة المرضى',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/patients",
     *     summary="Create a new patient",
     *     description="Create a new patient record",
     *     tags={"Patients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Patient created successfully"
     *     )
     * )
     */
    public function store(StorePatientRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['patient_id'] = $this->generatePatientId();
            $data['created_by'] = auth()->id();

            $patient = Patient::create($data);
            $patient->load('createdBy');

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المريض بنجاح',
                'data' => new PatientResource($patient)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة المريض',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/patients/{id}",
     *     summary="Get patient details",
     *     description="Get detailed information about a specific patient",
     *     tags={"Patients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function show(Patient $patient): JsonResponse
    {
        try {
            $patient->load(['createdBy', 'appointments.doctor.user', 'appointments.service', 'payments']);

            return response()->json([
                'success' => true,
                'message' => 'تم جلب بيانات المريض بنجاح',
                'data' => new PatientResource($patient)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات المريض',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/patients/{id}",
     *     summary="Update patient",
     *     description="Update patient information",
     *     tags={"Patients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient updated successfully"
     *     )
     * )
     */
    public function update(UpdatePatientRequest $request, Patient $patient): JsonResponse
    {
        try {
            $data = $request->validated();
            $patient->update($data);
            $patient->load('createdBy');

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات المريض بنجاح',
                'data' => new PatientResource($patient)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث بيانات المريض',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/patients/{id}",
     *     summary="Delete patient",
     *     description="Delete a patient record",
     *     tags={"Patients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Patient ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient deleted successfully"
     *     )
     * )
     */
    public function destroy(Patient $patient): JsonResponse
    {
        try {
            $patient->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المريض بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المريض',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generatePatientId(): string
    {
        $lastPatient = Patient::orderBy('id', 'desc')->first();
        $lastNumber = $lastPatient ? (int) substr($lastPatient->patient_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        
        return 'PAT' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
