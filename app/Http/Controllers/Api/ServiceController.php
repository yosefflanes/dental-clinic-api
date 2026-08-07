<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    /**
     * Menampilkan daftar layanan (Dilengkapi filter, search dan pagination)
     */

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Service::query()->latest();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            $limit = $request->integer('limit', 10);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Data layanan berhasil diambil.',
                'data'      => $query->paginate($limit)
            ]);
        } catch (\Exception $e) {
            Log::error('Service Index Error: ' . $e->getMessage());
            return response()->json([
                'status'    => 'error',
                'message'   => 'Terjadi kesalahan saat mengambil data layanan.'
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu layanan
     */
    public function show(int $id): JsonResponse
    {
        try {
            $service = Service::findOrFail($id);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Detail layanan berhasil diambil.',
                'data'      => $service
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Layanan tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * Membuat layanan baru (Admin)
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        try {
            $service = Service::create($request->validated());

            return response()->json([
                'status'    => 'success',
                'message'   => 'Layanan berhasil ditambahkan.',
                'data'      => $service,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Service Store Error: ' . $e->getMessage());

            return response()->json([
                'status'    => 'error',
                'message'   => 'Gagal menyimpan layanan.'
            ], 500);
        }
    }

    /**
     * Mengubah layanan (Admin)
     */
    public function update(StoreServiceRequest $request, int $id): JsonResponse
    {
        try {
            $service = Service::findOrFail($id);
            $service->update($request->validated());

            return response()->json([
                'status'    => 'success',
                'message'   => 'Layanan berhasil diperbarui.',
                'data'      => $service
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Layanan tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Service Update Error: ' . $e->getMessage());
            return response()->json([
                'status'    => 'error',
                'message'   => 'Gagal memperbarui layanan.'
            ], 500);
        }
    }

    /**
     * Menghapus layanan (Admin)
     */

    public function destroy(int $id): JsonResponse
    {
        try {
            Service::findOrFail($id)->delete();

            return response()->json([
                'status'    => 'success',
                'message'   => 'Layanan berhasil dihapus.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Layanan tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Service Destroy Error: ' . $e->getMessage());

            return response()->json([
                'status'    => 'error',
                'message'   => 'Gagal menghapus layanan.'
            ], 500);
        }
    }
}
