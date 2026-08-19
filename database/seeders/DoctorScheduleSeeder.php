<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctor = Doctor::first();

        if (!$doctor) {
            return;
        }

        $today = Carbon::now()->format('Y-m-d');
        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');

        $schedules = [
            // Jadwal Hari ini
            [
                'doctor_id' => $doctor->id,
                'practice_date' => $today,
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'is_available' => true,
            ],
            [
                'doctor_id' => $doctor->id,
                'practice_date' => $today,
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'is_available' => true,
            ],
            [
                'doctor_id' => $doctor->id,
                'practice_date' => $today,
                'start_time' => '13:00:00',
                'end_time' => '14:00:00',
                'is_available' => true,
            ],

            // Jadwal Besok
            [
                'doctor_id' => $doctor->id,
                'practice_date' => $tomorrow,
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'is_available' => true,
            ],
            [
                'doctor_id' => $doctor->id,
                'practice_date' => $tomorrow,
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'is_available' => true,
            ],
        ];

        foreach ($schedules as $schedule) {
            DoctorSchedule::create($schedule);
        }
    }
}
