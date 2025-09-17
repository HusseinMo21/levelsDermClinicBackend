<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 */
class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     *     path="/api/appointments",
     *     summary="Get all appointments",
     *     description="Get list of all appointments with operation number, patient name, service name, price, payment method, date, and notes",
     *     tags={"Appointments"},
     *     security={{"bearerAuth":{}}},
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            $query = Appointment::with(['patient', 'doctor.user', 'service', 'payments']);

            // Apply filters
            if ($status) {
                $query->where('status', $status);
            }

            if ($dateFrom) {
                $query->whereDate('appointment_date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('appointment_date', '<=', $dateTo);
            }

            $appointments = $query->orderBy('appointment_date', 'desc')
                ->paginate($perPage);

            $appointmentsData = $appointments->map(function ($appointment) {
                // Get payment method from the latest payment
                $paymentMethod = 'غير محدد';
                if ($appointment->payments && $appointment->payments->count() > 0) {
                    $latestPayment = $appointment->payments->sortByDesc('created_at')->first();
                    $paymentMethod = $latestPayment->payment_method ?? 'غير محدد';
                }

                // Map payment methods to Arabic
                $paymentMethodMap = [
                    'cash' => 'نقدي',
                    'card' => 'بطاقة ائتمان',
                    'bank_transfer' => 'تحويل بنكي',
                    'insurance' => 'تأمين',
                    'installment' => 'تقسيط',
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

                return [
                    'id' => $appointment->id,
                    'operation_number' => $appointment->operation_number,
                    'patient_name' => $appointment->patient->full_name ?? 'غير محدد',
                    'service_name' => $appointment->service->name ?? 'غير محدد',
                    'price' => $appointment->total_amount,
                    'payment_method' => $paymentMethodMap[$paymentMethod] ?? $paymentMethod,
                    'date' => $appointment->appointment_date->format('Y-m-d'),
                    'time' => $appointment->appointment_date->format('H:i'),
                    'notes' => $appointment->notes ?? '',
                    'status' => $statusMap[$appointment->status] ?? $appointment->status,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب المواعيد بنجاح',
                'data' => $appointmentsData,
                'pagination' => [
                    'current_page' => $appointments->currentPage(),
                    'last_page' => $appointments->lastPage(),
                    'per_page' => $appointments->perPage(),
                    'total' => $appointments->total(),
                    'from' => $appointments->firstItem(),
                    'to' => $appointments->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المواعيد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByDate(string $date): JsonResponse
    {
        try {
            $appointments = Appointment::with(['patient', 'doctor.user', 'service', 'payments'])
                ->whereDate('appointment_date', $date)
                ->orderBy('appointment_date', 'asc')
                ->get()
                ->map(function ($appointment) {
                    // Get payment method from the latest payment
                    $paymentMethod = 'غير محدد';
                    if ($appointment->payments && $appointment->payments->count() > 0) {
                        $latestPayment = $appointment->payments->sortByDesc('created_at')->first();
                        $paymentMethod = $latestPayment->payment_method ?? 'غير محدد';
                    }

                    // Map payment methods to Arabic
                    $paymentMethodMap = [
                        'cash' => 'نقدي',
                        'card' => 'بطاقة ائتمان',
                        'bank_transfer' => 'تحويل بنكي',
                        'insurance' => 'تأمين',
                        'installment' => 'تقسيط',
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

                    return [
                        'id' => $appointment->id,
                        'operation_number' => $appointment->operation_number,
                        'patient_name' => $appointment->patient->full_name ?? 'غير محدد',
                        'service_name' => $appointment->service->name ?? 'غير محدد',
                        'price' => $appointment->total_amount,
                        'payment_method' => $paymentMethodMap[$paymentMethod] ?? $paymentMethod,
                        'date' => $appointment->appointment_date->format('Y-m-d'),
                        'time' => $appointment->appointment_date->format('H:i'),
                        'notes' => $appointment->notes ?? '',
                        'status' => $statusMap[$appointment->status] ?? $appointment->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب مواعيد التاريخ المحدد بنجاح',
                'data' => $appointments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب مواعيد التاريخ المحدد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByDoctor(int $doctor_id): JsonResponse
    {
        try {
            $appointments = Appointment::with(['patient', 'doctor.user', 'service', 'payments'])
                ->where('doctor_id', $doctor_id)
                ->orderBy('appointment_date', 'desc')
                ->get()
                ->map(function ($appointment) {
                    // Get payment method from the latest payment
                    $paymentMethod = 'غير محدد';
                    if ($appointment->payments && $appointment->payments->count() > 0) {
                        $latestPayment = $appointment->payments->sortByDesc('created_at')->first();
                        $paymentMethod = $latestPayment->payment_method ?? 'غير محدد';
                    }

                    // Map payment methods to Arabic
                    $paymentMethodMap = [
                        'cash' => 'نقدي',
                        'card' => 'بطاقة ائتمان',
                        'bank_transfer' => 'تحويل بنكي',
                        'insurance' => 'تأمين',
                        'installment' => 'تقسيط',
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

                    return [
                        'id' => $appointment->id,
                        'operation_number' => $appointment->operation_number,
                        'patient_name' => $appointment->patient->full_name ?? 'غير محدد',
                        'service_name' => $appointment->service->name ?? 'غير محدد',
                        'price' => $appointment->total_amount,
                        'payment_method' => $paymentMethodMap[$paymentMethod] ?? $paymentMethod,
                        'date' => $appointment->appointment_date->format('Y-m-d'),
                        'time' => $appointment->appointment_date->format('H:i'),
                        'notes' => $appointment->notes ?? '',
                        'status' => $statusMap[$appointment->status] ?? $appointment->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب مواعيد الطبيب بنجاح',
                'data' => $appointments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب مواعيد الطبيب',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'required|exists:doctors,id',
                'service_id' => 'required|exists:services,id',
                'appointment_date' => 'required|date|after:now',
                'notes' => 'nullable|string|max:1000',
                'type' => 'required|in:consultation,treatment,follow_up',
            ], [
                'patient_id.required' => 'معرف المريض مطلوب',
                'patient_id.exists' => 'المريض غير موجود',
                'doctor_id.required' => 'معرف الطبيب مطلوب',
                'doctor_id.exists' => 'الطبيب غير موجود',
                'service_id.required' => 'معرف الخدمة مطلوب',
                'service_id.exists' => 'الخدمة غير موجودة',
                'appointment_date.required' => 'تاريخ الموعد مطلوب',
                'appointment_date.date' => 'تاريخ الموعد غير صحيح',
                'appointment_date.after' => 'تاريخ الموعد يجب أن يكون في المستقبل',
                'type.required' => 'نوع الموعد مطلوب',
                'type.in' => 'نوع الموعد غير صحيح',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات المدخلة غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            $service = Service::find($request->service_id);
            $appointmentDate = Carbon::parse($request->appointment_date);
            $endTime = $appointmentDate->copy()->addMinutes($service->duration_minutes);

            $appointment = Appointment::create([
                'appointment_id' => $this->generateAppointmentId(),
                'operation_number' => $this->generateOperationNumber(),
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'service_id' => $request->service_id,
                'appointment_date' => $appointmentDate,
                'end_time' => $endTime,
                'status' => 'scheduled',
                'type' => $request->type,
                'total_amount' => $service->price,
                'discount_amount' => 0,
                'payment_required' => true,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الموعد بنجاح',
                'data' => [
                    'id' => $appointment->id,
                    'operation_number' => $appointment->operation_number,
                    'appointment_id' => $appointment->appointment_id,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الموعد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $appointment = Appointment::with(['patient', 'doctor.user', 'service', 'payments.processedBy'])
                ->findOrFail($id);

            // Get payment method and processed by from the latest payment
            $paymentMethod = 'غير محدد';
            $paymentProcessedBy = 'غير محدد';
            if ($appointment->payments && $appointment->payments->count() > 0) {
                $latestPayment = $appointment->payments->sortByDesc('created_at')->first();
                $paymentMethod = $latestPayment->payment_method ?? 'غير محدد';
                $paymentProcessedBy = $latestPayment->processedBy->name ?? 'غير محدد';
            }

            // Map payment methods to Arabic
            $paymentMethodMap = [
                'cash' => 'نقدي',
                'card' => 'بطاقة ائتمان',
                'bank_transfer' => 'تحويل بنكي',
                'insurance' => 'تأمين',
                'installment' => 'تقسيط',
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

            $appointmentData = [
                'id' => $appointment->id,
                'operation_number' => $appointment->operation_number,
                'appointment_id' => $appointment->appointment_id,
                
                // Patient Information
                'patient_name' => $appointment->patient->full_name ?? 'غير محدد',
                'phone_number' => $appointment->patient->phone ?? 'غير محدد',
                
                // Doctor Information
                'doctor_name' => $appointment->doctor->user->name ?? 'غير محدد',
                'doctor_specialization' => $appointment->doctor->specialization ?? 'غير محدد',
                
                // Appointment Information
                'date' => $appointment->appointment_date->format('Y-m-d'),
                'time' => $appointment->appointment_date->format('H:i'),
                'end_time' => $appointment->end_time->format('H:i'),
                
                // Service Information
                'service_name' => $appointment->service->name ?? 'غير محدد',
                'service_description' => $appointment->service->description ?? '',
                
                // Status and Condition
                'condition' => $statusMap[$appointment->status] ?? $appointment->status,
                'status' => $appointment->status, // Keep original status for backend use
                
                // Payment Information
                'payment_method' => $paymentMethodMap[$paymentMethod] ?? $paymentMethod,
                'price' => $appointment->total_amount,
                'discount_amount' => $appointment->discount_amount,
                'payment_processed_by' => $paymentProcessedBy,
                
                // Additional Information
                'notes' => $appointment->notes ?? '',
                'type' => $appointment->type,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];

            return response()->json([
                'success' => true,
                'message' => 'تم جلب تفاصيل الموعد بنجاح',
                'data' => $appointmentData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب تفاصيل الموعد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'appointment_date' => 'nullable|date|after:now',
                'notes' => 'nullable|string|max:1000',
                'status' => 'nullable|in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            ], [
                'appointment_date.date' => 'تاريخ الموعد غير صحيح',
                'appointment_date.after' => 'تاريخ الموعد يجب أن يكون في المستقبل',
                'status.in' => 'حالة الموعد غير صحيحة',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات المدخلة غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];
            if ($request->has('appointment_date')) {
                $appointmentDate = Carbon::parse($request->appointment_date);
                $updateData['appointment_date'] = $appointmentDate;
                $updateData['end_time'] = $appointmentDate->copy()->addMinutes($appointment->service->duration_minutes);
            }
            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }

            $appointment->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الموعد بنجاح',
                'data' => [
                    'id' => $appointment->id,
                    'operation_number' => $appointment->operation_number,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الموعد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الموعد بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الموعد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            ], [
                'status.required' => 'حالة الموعد مطلوبة',
                'status.in' => 'حالة الموعد غير صحيحة',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'البيانات المدخلة غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            $appointment->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الموعد بنجاح',
                'data' => [
                    'id' => $appointment->id,
                    'operation_number' => $appointment->operation_number,
                    'status' => $request->status,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الموعد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateAppointmentId(): string
    {
        $lastAppointment = Appointment::orderBy('id', 'desc')->first();
        $lastNumber = $lastAppointment ? (int) substr($lastAppointment->appointment_id, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'APT' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    private function generateOperationNumber(): string
    {
        $lastAppointment = Appointment::orderBy('id', 'desc')->first();
        $lastNumber = $lastAppointment ? (int) substr($lastAppointment->operation_number, 3) : 0;
        $newNumber = $lastNumber + 1;
        return 'OPR' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
