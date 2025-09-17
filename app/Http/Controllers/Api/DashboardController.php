<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

/**
 * @OA\Tag(name="Dashboard")
 */
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/kpis",
     *     summary="Get dashboard KPIs",
     *     description="Get key performance indicators for the dashboard",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="KPIs retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="total_patients", type="integer", example=145),
     *                 @OA\Property(property="total_doctors", type="integer", example=19),
     *                 @OA\Property(property="today_appointments", type="integer", example=17),
     *                 @OA\Property(property="today_payments", type="number", format="float", example=3750.00),
     *                 @OA\Property(property="monthly_revenue", type="number", format="float", example=125000.00),
     *                 @OA\Property(property="active_patients", type="integer", example=120),
     *                 @OA\Property(property="completed_appointments", type="integer", example=15)
     *             )
     *         )
     *     )
     * )
     */
    public function getKPIs(): JsonResponse
    {
        try {
            $today = Carbon::today();
            
            $kpis = [
                'total_patients' => Patient::count(),
                'total_doctors' => Doctor::count(),
                'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
                'today_payments' => Payment::whereDate('payment_date', $today)
                    ->where('status', 'completed')
                    ->sum('total_amount'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'تم جلب مؤشرات الأداء بنجاح',
                'data' => $kpis
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب مؤشرات الأداء',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/recent-appointments",
     *     summary="Get recent appointments",
     *     description="Get list of recent appointments for the dashboard",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of appointments to retrieve",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Recent appointments retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_name", type="string", example="Ahmed Mohammed"),
     *                     @OA\Property(property="doctor_name", type="string", example="Dr. Sarah Ahmed"),
     *                     @OA\Property(property="appointment_time", type="string", example="12:00"),
     *                     @OA\Property(property="status", type="string", example="confirmed"),
     *                     @OA\Property(property="payment_method", type="string", example="cash")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getRecentAppointments(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            
            $appointments = Appointment::with(['patient', 'doctor.user', 'service', 'payments'])
                ->whereDate('appointment_date', '>=', Carbon::today())
                ->orderBy('appointment_date', 'asc')
                ->limit($limit)
                ->get()
                ->map(function ($appointment) {
                    // Get payment method from the latest payment
                    $paymentMethod = 'غير محدد';
                    if ($appointment->payments && $appointment->payments->count() > 0) {
                        $latestPayment = $appointment->payments->sortByDesc('created_at')->first();
                        $paymentMethod = $latestPayment->payment_method ?? 'غير محدد';
                    }

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
                        'patient_name' => $appointment->patient->full_name ?? 'غير محدد',
                        'doctor_name' => $appointment->doctor->user->name ?? 'غير محدد',
                        'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                        'appointment_time' => $appointment->appointment_date->format('H:i'),
                        'status' => $statusMap[$appointment->status] ?? $appointment->status,
                        'payment_method' => $paymentMethod,
                        'total_amount' => $appointment->total_amount,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب المواعيد الأخيرة بنجاح',
                'data' => $appointments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المواعيد الأخيرة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/today-appointments",
     *     summary="Get today's appointments",
     *     description="Get list of today's appointments with patient, doctor, time, status, and payment method",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تم جلب مواعيد اليوم بنجاح"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="patient_name", type="string", example="نور أحمد"),
     *                     @OA\Property(property="doctor_name", type="string", example="د. محمد حسن"),
     *                     @OA\Property(property="appointment_time", type="string", example="12:00"),
     *                     @OA\Property(property="status", type="string", example="تم التنفيذ"),
     *                     @OA\Property(property="payment_method", type="string", example="نقدي")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getTodayAppointments(): JsonResponse
    {
        try {
            $today = Carbon::today();
            
            $appointments = Appointment::with(['patient', 'doctor.user', 'payments'])
                ->whereDate('appointment_date', $today)
                ->orderBy('appointment_date', 'asc')
                ->get()
                ->map(function ($appointment) {
                    // Get payment method from the latest payment
                    $paymentMethod = 'غير محدد';
                    if ($appointment->payments && $appointment->payments->count() > 0) {
                        $latestPayment = $appointment->payments->sortByDesc('created_at')->first();
                        $paymentMethod = $latestPayment->payment_method ?? 'غير محدد';
                    }

                    // Map status to Arabic
                    $statusMap = [
                        'scheduled' => 'مجدول',
                        'confirmed' => 'مؤكد',
                        'in_progress' => 'قيد التنفيذ',
                        'completed' => 'تم التنفيذ',
                        'cancelled' => 'ألغي',
                        'no_show' => 'لم يحضر',
                    ];

                    // Map payment methods to Arabic
                    $paymentMethodMap = [
                        'cash' => 'نقدي',
                        'card' => 'بطاقة ائتمان',
                        'visa' => 'فيزا',
                        'mastercard' => 'ماستركارد',
                        'loyalty_points' => 'نقاط الولاء',
                        'tamara' => 'تمارا',
                        'bank_transfer' => 'تحويل بنكي',
                    ];

                    return [
                        'id' => $appointment->id,
                        'patient_name' => $appointment->patient->full_name ?? 'غير محدد',
                        'doctor_name' => $appointment->doctor->user->name ?? 'غير محدد',
                        'appointment_time' => $appointment->appointment_date->format('H:i'),
                        'status' => $statusMap[$appointment->status] ?? $appointment->status,
                        'payment_method' => $paymentMethodMap[$paymentMethod] ?? $paymentMethod,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'تم جلب مواعيد اليوم بنجاح',
                'data' => $appointments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب مواعيد اليوم',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
