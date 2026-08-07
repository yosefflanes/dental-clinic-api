<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'doctor_schedule_id',
        'complaint',
        'status'
    ];

    // Kardinalitas: Appointment hanya milik 1 user (N:1)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Kardinalitas: Appointment memilih 1 service (N:1)
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Kardinalitas: Appointment terikat pada 1 jadwal dokter (1:1)
    public function doctorSchedule()
    {
        return $this->belongsTo(DoctorSchedule::class);
    }

    // Kardinalitas: Appointment menghasilkan 1 pembayaran (1:1)
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
