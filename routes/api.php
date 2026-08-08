<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorScheduleController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|============================================================================
| Public Routes
|============================================================================
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::post('/payments/notification', [PaymentController::class, 'notification']);


/*
|============================================================================
| Protected Routes - Harus Login (Pasien)
|============================================================================
*/
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function(Request $request) {
        return response()->json([
            'status'    => 'success',
            'data'      => $request->user()
        ]);
    });

    Route::get('/doctor-schedules', [DoctorScheduleController::class, 'index']);

    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/my', [AppointmentController::class, 'myAppointment']);
    Route::patch('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/payments', [PaymentController::class, 'store']);

    /*
    |============================================================================
    | Admin Protected - Hanya admin
    |============================================================================
    */
    Route::middleware('check_role:admin')->group(function () {
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{id}', [ServiceController::class, 'update']);
        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
        Route::post('/doctor-schedules', [DoctorScheduleController::class, 'store']);
        Route::patch('/doctor-schedules/{id}/availability', [DoctorScheduleController::class, 'updateAvailability']);
        Route::get('/appointments', [AppointmentController::class, 'index']);
        Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
        Route::get('/reports', [ReportController::class, 'index']);
    });
});
