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
class SimplePatientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
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

            // Search functionality
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

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by gender
            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            // Sort
            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            // Pagination
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
            
            // Generate unique patient ID
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

    /**
     * Generate unique patient ID
     */
    private function generatePatientId(): string
    {
        $lastPatient = Patient::orderBy('id', 'desc')->first();
        $lastNumber = $lastPatient ? (int) substr($lastPatient->patient_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        
        return 'PAT' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
