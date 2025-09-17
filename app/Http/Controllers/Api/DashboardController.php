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
 */
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     *     path="/api/dashboard/kpis",
     *     summary="Get dashboard KPIs",
     *     description="Get key performance indicators for the dashboard",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *         response=200,
     *         description="Successful operation",
     *                 property="data",
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
     *     path="/api/dashboard/recent-appointments",
     *     summary="Get recent appointments",
     *     description="Get list of recent appointments for the dashboard",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *         name="limit",
     *         in="query",
     *         description="Number of appointments to retrieve",
     *         required=false,
     *     ),
     *         response=200,
     *         description="Successful operation",
     *                 property="data",
     *                 type="array",
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
     *     path="/api/dashboard/today-appointments",
     *     summary="Get today's appointments",
     *     description="Get list of today's appointments with patient, doctor, time, status, and payment method",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *         response=200,
     *         description="Successful operation",
     *                 property="data",
     *                 type="array",
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
