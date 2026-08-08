<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
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
            $estimatedRevenue = Appointment::where('appointments.status', 'selesai')
                ->join('services', 'appointments.service_id', '=', 'services.id')
                ->sum('services.price');

            // Layanan Terlaris (Top 5)
            $topServices = Appointment::select('service_id', DB::raw('COUNT(id) as total_appointment'))
                ->groupBy('service_id')
                ->orderBy('total_appointment', 'desc')
                ->with('service')
                ->take(5)
                ->get();

            // Jadwal Paling Ramai
            $busiestSchedule = Appointment::select('doctor_schedule_id', DB::raw('COUNT(id) as total_appointment'))
                ->groupBy('doctor_schedule_id')
                ->orderBy('total_appointment', 'desc')
                ->with('doctorSchedule')
                ->take(5)
                ->get();

            return response()->json([
                'status'    => 'success',
                'message'   => 'Laporan dan analisis data klinik berhasil diambil.',
                'data'      => [
                    'summary'   => [
                        'total_appointments'        => $totalAppointments,
                        'completed_appointments'    => $completedAppointments,
                        'pending_appointments'      => $pendingAppointments,
                        'canceled_appointments'     => $canceledAppointments,
                        'estimated_revenue'         => (float) $estimatedRevenue,
                    ],
                    'top_services'      => $topServices,
                    'busiest_schedule'  => $busiestSchedule,
                ]
            ]);
        } catch (\Exception $e){
            Log::error("Report Index Error: " . $e->getMessage());
            return response()->json([
                'status'    => 'error',
                'message'   => 'Terjadi kesalahan sistem saat mengambil data laporan.'
            ], 500);
        }
    }
}
