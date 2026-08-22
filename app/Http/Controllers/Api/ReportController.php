<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            // Ringkasan Status Antrian
            $totalAppointments = Appointment::count();
            $completedAppointments = Appointment::where('status', 'selesai')->count();
            $pendingAppointments = Appointment::where('status', 'pending')->count();
            $canceledAppointments = Appointment::where('status', 'batal')->count();

            // Estimasi Revenue (Hanya dihitung dari antrian yang selesai)
            $estimatedRevenue = Payment::where('status', 'settlement')->sum('amount');

            // Layanan Terlaris (Top 5)
            $topServices = Appointment::with('service')
                ->select('service_id', \Illuminate\Support\Facades\DB::raw('count(*) as total_appointment'))
                ->groupBy('service_id')
                ->take(5)
                ->get();

            $busiestSchedule = Appointment::with('doctorSchedule')
                ->select('doctor_schedule_id', \Illuminate\Support\Facades\DB::raw('count(*) as total_appointment'))
                ->groupBy('doctor_schedule_id')
                ->take(5)
                ->get();

            return response()->json([
                'status'    => 'success',
                'message'   => 'Laporan dan analisis data klinik berhasil diambil.',
                'data'      => [
                    'summary'   => [
                        'total_appointments'        => (int) $totalAppointments,
                        'completed_appointments'    => (int) $completedAppointments,
                        'pending_appointments'      => (int) $pendingAppointments,
                        'canceled_appointments'     => (int) $canceledAppointments,
                        'estimated_revenue'         => (float) $estimatedRevenue,
                    ],
                    'top_services'      => $topServices,
                    'busiest_schedule'  => $busiestSchedule,
                ]
            ]);
        } catch (\Exception $e){
            Log::error("Report Error Detail: " . $e->getMessage() . " on line " . $e->getLine());
            return response()->json([
                'status'    => 'error',
                'message'   => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
