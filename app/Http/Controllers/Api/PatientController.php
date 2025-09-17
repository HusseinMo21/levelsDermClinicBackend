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

class PatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

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
