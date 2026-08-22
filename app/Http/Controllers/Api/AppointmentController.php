<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{

    /**
     * Melihat semua appointment (khusus admin)
     */
    public function index(Request $request): JsonResponse
    {
        $appointments = Appointment::with(['user', 'doctorSchedule.doctor', 'service'])
            ->latest()
            ->paginate($request->integer('limit', 10));

        return response()->json([
            'status' => 'success',
            'message' => 'Data appointment berhasil diambil.',
            'data' => $appointments
        ]);
    }

    /**
     * Pasien membuat Janji Temu baru
     */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = $request->user();

            // Gunakan Pessimistic Locking
            return DB::transaction(function () use ($validated, $user) {
                $schedule = DoctorSchedule::where('id', $validated['doctor_schedule_id'])
                    ->lockForUpdate()
                    ->first();
                if (!$schedule) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Jadwal dokter tidak ditemukan.'
                    ], 404);
                }

                if (!$schedule->is_available) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Maaf, jadwal sudah penuh atau ditutup.'
                    ], 422);
                }

                // Buat appointment/janji temu
                $appointment = Appointment::create([
                    'user_id' => $user->id,
                    'doctor_schedule_id' => $validated['doctor_schedule_id'],
                    'service_id' => $validated['service_id'],
                    'complaint' => $validated['complaint'] ?? null,
                    'status' => 'pending',
                ]);

                $schedule->update(['is_available' => false]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Janji temu berhasil dibuat.',
                    'data' => $appointment->load(['doctorSchedule', 'service'])
                ], 201);
            });

        } catch (\Exception $e) {
            Log::error('Appointment Store Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat appointment.'
            ], 500);
        }
    }

    /**
     * Pasien melihat appointment / janji temu
     */
    public function myAppointment(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Ambil data user login, diurutkan dari yang terbaru
            $appointments = Appointment::where('user_id', $user->id)
                ->with(['doctorSchedule.doctor', 'service', 'payment'])
                ->latest()
                ->paginate($request->integer('limit', 10));

            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat janji temu berhasil diambil.',
                'data' => $appointments
            ]);
        } catch (\Exception $e) {
            Log::error('My Appointment Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil riwayat janji temu.'
            ], 500);
        }
    }

    /**
     * Mengubah status janji temu (Admin)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,selesai,batal']
        ]);

        $appointment->update([
            'status' => $validated['status']
        ]);

        // Jika admin membatalkan antrian pasien, jadwal harus dibuka kembali
        if ($validated['status'] === 'batal') {
            $appointment->doctorSchedule()->update(['is_available' => true]);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Status janji temu berhasil diubah menjadi {$validated['status']}.",
            'data' => $appointment
        ]);
    }

    /**
     * 5. Membatalkan janji temu (Pasien)
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);

        // Validasi 1: Memastikan pembatalan dilakukan oleh user yang membuat appoinment
        if ($appointment->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki hak untuk membatalkan janji temu ini.',
            ], 403);
        }

        // Validasi 2: Antrian belum diproses atau berstatus pending
        if ($appointment->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Appointment tidak bisa dibatalkan.'
            ], 422);
        }

        $appointment->update(['status' => 'batal']);

        $appointment->doctorSchedule()->update(['is_available' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Janji temu berhasil dibatalkan.',
            'data' => $appointment
        ]);
    }
}
