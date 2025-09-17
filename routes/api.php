<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {

        // Authentication routes (protected)
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

    // Patient Management API (العملاء) - Available to all authenticated users
    Route::apiResource('patients', PatientController::class);
    Route::get('/patients/search/{term}', [PatientController::class, 'search']);

    // Appointment Management API (المواعيد)
    Route::apiResource('appointments', AppointmentController::class);
    Route::get('/appointments/by-date/{date}', [AppointmentController::class, 'getByDate']);
    Route::get('/appointments/by-doctor/{doctor_id}', [AppointmentController::class, 'getByDoctor']);
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);

    // Service Management API (الخدمات)
    Route::apiResource('services', ServiceController::class);
    Route::get('/services/by-category/{category}', [ServiceController::class, 'getByCategory']);


    // Payment Management API (المدفوعات)
    Route::apiResource('payments', PaymentController::class);
    Route::get('/payments/by-patient/{patient_id}', [PaymentController::class, 'getByPatient']);
    Route::get('/payments/by-date/{date}', [PaymentController::class, 'getByDate']);
    Route::get('/payments/daily-report/{date}', [PaymentController::class, 'getDailyReport']);

    // Role-specific endpoints
    Route::middleware('role:admin')->group(function () {
        // Dashboard routes - Admin only
        Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
        Route::get('/dashboard/recent-appointments', [DashboardController::class, 'getRecentAppointments']);
        Route::get('/dashboard/today-appointments', [DashboardController::class, 'getTodayAppointments']);
        Route::get('/dashboard/kpis', [DashboardController::class, 'getKPIs']);
        Route::get('/admin/all-stats', [DashboardController::class, 'getAllStats']);
        
        // Clients API - Admin only (Dashboard Interface)
        Route::get('/clients', [PatientController::class, 'clients']);
        Route::get('/clients/{id}/details', [PatientController::class, 'clientDetails']);
        
                // Doctor Management API - Admin only
                Route::get('/doctors/specializations', [DoctorController::class, 'getSpecializations']);
                Route::get('/doctors/search/{term}', [DoctorController::class, 'search']);
                Route::get('/doctors/by-specialization/{specialization}', [DoctorController::class, 'getBySpecialization']);
                Route::get('/doctors/{id}/info', [DoctorController::class, 'getDoctorInfo']);
                Route::apiResource('doctors', DoctorController::class);
                
                // Inventory Management API - Admin only
                Route::get('/inventory/dashboard', [InventoryController::class, 'getDashboard']);
                Route::get('/inventory/withdrawals', [InventoryController::class, 'getWithdrawals']);
                Route::get('/inventory/notifications', [InventoryController::class, 'getNotifications']);
                Route::get('/inventory/requests', [InventoryController::class, 'getRequests']);
                Route::get('/inventory/expired', [InventoryController::class, 'getExpiredItems']);
                Route::post('/inventory/items', [InventoryController::class, 'store']);
    });

    Route::middleware('role:receptionist')->group(function () {
        Route::get('/receptionist/daily-appointments', [AppointmentController::class, 'getDailyAppointments']);
        Route::post('/receptionist/quick-booking', [AppointmentController::class, 'quickBooking']);
    });

    Route::middleware('role:doctor')->group(function () {
        Route::get('/doctor/my-appointments', [AppointmentController::class, 'getDoctorAppointments']);
        Route::patch('/doctor/appointments/{appointment}/complete', [AppointmentController::class, 'completeAppointment']);
    });

    Route::middleware('role:customer-service')->group(function () {
        Route::get('/customer-service/leads', [PatientController::class, 'getLeads']);
        Route::post('/customer-service/convert-lead', [PatientController::class, 'convertLead']);
    });

    Route::middleware('role:patient')->group(function () {
        Route::get('/patient/my-profile', [PatientController::class, 'getMyProfile']);
        Route::get('/patient/my-appointments', [AppointmentController::class, 'getMyAppointments']);
        Route::get('/patient/my-payments', [PaymentController::class, 'getMyPayments']);
    });
});

// Authentication routes (public)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Test route for Swagger
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'data' => [
            'timestamp' => now(),
            'version' => '1.0.0'
        ]
    ]);
});
