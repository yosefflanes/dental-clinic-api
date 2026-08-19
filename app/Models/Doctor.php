<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'name',
        'specialization',
        'phone',
    ];

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }
}
