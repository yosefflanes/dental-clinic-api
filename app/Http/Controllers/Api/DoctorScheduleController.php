<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorScheduleRequest;
use App\Models\DoctorSchedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DoctorScheduleController extends Controller
{

    /**
     * Menampilkan daftar jadwal (Dilengkapi filter tanggal dan pagination)
     */

    public function index(Request $request): JsonResponse
    {
        try {
            $query = DoctorSchedule::with('doctor')
                ->orderBy('practice_date', 'asc')
                ->orderBy('start_time', 'asc');

            // Filter by tanggal tertentu
            if ($request->filled('date')) {
                $query->whereDate('practice_date', $request->date);
            } else if ($request->filled('practice_date')) {
                $query->whereDate('practice_date', $request->practice_date);
            }

            // Filter ketersediaan
            if ($request->filled('is_available')) {
                $query->where('is_available', $request->boolean('is_available'));
            }

            $limit = $request->integer('limit', 10);

            return response()->json([
                'status' => 'success',
                'message' => 'Data jadwal berhasil diambil.',
                'data' => $query->paginate($limit)
            ]);
        } catch (\Exception $e) {
            Log::error('Schedule Index ErrorL: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data jadwal.'
            ], 500);
        }
    }

    /**
     * Menambahkan jadwal baru (Hanya admin)
     */

    public function store(StoreDoctorScheduleRequest $request): JsonResponse
    {
        try {
            $schedule = DoctorSchedule::create($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal berhasil ditambahkan.',
                'data' => $schedule
            ], 201);
        } catch (\Exception $e) {
            Log::error('Schedule Store Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambahkan jadwal praktek.'
            ], 500);
        }
    }

    /**
     * Mengubah status ketersediaan jadwal (Hanya admin) - tutup/buka antrian
     */

    public function updateAvailability(Request $request, int $id): JsonResponse
    {
        try {
            $schedule = DoctorSchedule::findOrFail($id);
            $validated = $request->validate([
                'is_available' => ['required', 'boolean']
            ]);

            $schedule->update(['is_available' => $validated['is_available']]);

            // Pesan tergantung status
            $statusText = $validated['is_available'] ? 'dibuka' : 'ditutup';

            return response()->json([
                'status' => 'success',
                'message' => "Jadwal berhasil $statusText.",
                'data' => $schedule
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Schedule updateAvailability Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui jadwal praktek.'
            ], 500);
        }
    }
}
