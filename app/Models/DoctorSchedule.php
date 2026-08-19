<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'practice_date',
        'start_time',
        'end_time',
        'is_available'
    ];

    protected function casts(): array
    {
        return [
            'practice_date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    // Kardinalitas: 1 jadwal Dokter hanya punya 1 appointment (1:1)
    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }

    // Kardinalitas: Jadwal ini milik 1 dokter 
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
