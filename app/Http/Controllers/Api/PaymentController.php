<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    /**
     * Meminta snap token dari Midtrans untuk pembayaran Appointment
     */

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'appointment_id'    => ['required', 'exists:appointment,id'],
        ]);

        $appointment = Appointment::with(['service', 'payment'])->findOrFail($validated['appointment_id']);

        // Validasi Otorisasi (Hanya pemilik appointment yang bisa bayar)
        if ($appointment->user_id !== $request->user()->id){
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses ke tagihan ini.'
            ], 403);
        }

        // Validasi Status (Hanya yang pending yang bisa dibayar)
        if ($appointment->status !== 'pending'){
            return response()->json([
                'status'    => 'error',
                'message'   => 'Appointment ini tidak dalam status menunggu pembayaran.'
            ], 422);
        }

        // Cegah pembuatan token ganda
        // Jika sudah ada payment dan tokennya masih ada, kembalikan token yang lama
        if ($appointment->payment && $appointment->payment->snap_token ){
            if ($appointment->payment->status === 'settlement') {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Tagihan untuk appointment ini sudah lunas.'
                ], 422);
            }

            return response()->json([
                'status'    => 'success',
                'message'   => 'Token pembayaran aktif berhasil diambil.',
                'data'      => [
                    'snap_token'    => $appointment->payment->snap_token,
                    'payment'       => $appointment->payment,
                ]
            ]);
        }

        try {
            // Konfigurasi Midtrans dari file config/midtrans.php
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Siapkan Parameter Pembayaran
            // Membuat order_id unik = DENTIST-{id_appointment}-{waktu_saat_ini}
            $orderId = 'DENTIST-' . $appointment->id . '-' . time();
            $grossAmount = $appointment->service->price;

            $params = [
                'transaction_details'   => [
                    'order_id'      => $orderId,
                    'gross_amount'  => (int) $grossAmount,
                ],
                'costumer_details'      => [
                    'first_name'    => $request->user()->name,
                    'email'         => $request->user()->email,
                    'phone'         => $request->user()->phone,
                ],
                'item_details'          => [
                    [
                        'id'        => $appointment->service->id,
                        'price'     => (int) $grossAmount,
                        'quantity'  => 1,
                        'name'      => $appointment->service->name,
                    ]
                ]
            ];

            // Tembak API Midtrans untuk mendapatkan snap_token
            $snapToken = Snap::getSnapToken($params);

            // Simpan ke database
            $payment = Payment::create([
                'appointment_id'    => $appointment->id,
                'amount'            => $grossAmount,
                'snap_token'        => $snapToken,
                'status'            => 'pending',
            ]);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Token pembayaran midtrans berhasil dibuat.',
                'data'      => [
                    'snap_token'    => $snapToken,
                    'payment'       => $payment
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            return response()->json([
                'status'    => 'error',
                'message'   => 'Gagal terhubung ke layanan midtrans.'
            ], 500);
        }
    }

    public function notifications(Request $request): JsonResponse
    {
        try {
            // Konfigurasi ulang kunci Midtrans
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');

            // Ambil data notifikasi resmi dari library Midtrans
            $notif = new \Midtrans\Notification();

            $transactionStatus  = $notif->transaction_status;
            $payment_type       = $notif->payment_type;
            $orderId            = $notif->order_id;
            $fraudStatus        = $notif->fraud_status;

            // Ekstraks kembali ID Appointment dari format order_id
            // Format : DENTIST-{id_appointment}-{waktu_saat_ini}
            $exploded = explode('-', $orderId);
            $appointmentId = $exploded[1] ?? null;

            if (!$appointmentId){
                return response()->json(['status' => 'error', 'message' => 'Order ID tidak valid.'], 400);
            }

            // Cari data payment berdasarkan appointment_id
            $payment = Payment::where('appointment_id', $appointmentId)->first();

            if (!$payment) {
                return response()->json(['status'   => 'error', 'message'   => 'Data pembayaran tidak ditemukan.']);
            }

            // Logika perubahan status berdasarkan respons Midtrans
            if ($transactionStatus == 'capture'){
                if ($fraudStatus == 'challenge'){
                    $payment->status = 'pending';
                } else if ($fraudStatus == 'accept'){
                    $payment->status = 'settlement';
                }
            } else if ($transactionStatus == 'settlement'){
                $payment->status = 'settlement';
            } else if ($transactionStatus == 'pending'){
                $payment->status = 'pending';
            } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel'){
                $payment->status = 'cancel';

                // Jika dibatalkan/expire, kita bisa update juga status appointment nya menjadi batal
                $appointment = Appointment::find($appointmentId);
                if ($appointment){
                    $appointment->status = 'batal';
                    $appointment->save();

                    // Kembalikan ketersediaan jadwal dokter
                    if ($appointment->doctorSchedule){
                        $appointment->doctorSchedule->update(['is_available' => true]);
                    }
                }
            }

            // Simpan metode pembayaran yang dipilih dan transacction_id
            $payment->method = $payment_type;
            $payment->transaction_id = $notif->transaction_id;
            $payment->save();

            return response()->json([
                'status'    => 'success',
                'message'   => 'Notofikasi Midtrans berhasil diproses.'
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json([
                'status'    => 'error',
                'message'   => 'Terjadi kesalahan pada server webhook.'
            ]);
        }
    }
}
