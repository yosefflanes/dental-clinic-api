<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'method',
        'amount',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2'
        ];
    }

    // Kardinalitas: Payment milik 1 transaksi appointment (1:1)
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
