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
        $drIndriani = Doctor::where('name', 'LIKE', '%Puspita Indriani%')->first();
        $drBudi = Doctor::where('name', 'LIKE', '%Budi Santoso%')->first();

        if (!$drIndriani || !$drBudi)
            return;

        for ($i = 0; $i < 28; $i++) {
            $date = Carbon::now()->addDays($i);
            $dayOfWeek = $date->dayOfWeek;

            if ($dayOfWeek >= Carbon::MONDAY && $dayOfWeek <= Carbon::THURSDAY) {
                for ($hour = 9; $hour < 15; $hour++) {
                    DoctorSchedule::create([
                        'doctor_id' => $drIndriani->id,
                        'practice_date' => $date->format('Y-m-d'),
                        'start_time' => sprintf('%02d:00:00', $hour),
                        'end_time' => sprintf('%02d:00:00', $hour + 1),
                        'is_available' => true,
                    ]);
                }

                for ($hour = 15; $hour < 21; $hour++) {
                    DoctorSchedule::create([
                        'doctor_id' => $drBudi->id,
                        'practice_date' => $date->format('Y-m-d'),
                        'start_time' => sprintf('%02d:00:00', $hour),
                        'end_time' => sprintf('%02d:00:00', $hour + 1),
                        'is_available' => true,
                    ]);
                }
            }
            if ($dayOfWeek === Carbon::FRIDAY || $dayOfWeek === Carbon::SATURDAY) {
                for ($hour = 17; $hour < 21; $hour++) {
                    DoctorSchedule::create([
                        'doctor_id' => $drIndriani->id,
                        'practice_date' => $date->format('Y-m-d'),
                        'start_time' => sprintf('%02d:00:00', $hour),
                        'end_time' => sprintf('%02d:00:00', $hour + 1),
                        'is_available' => true,
                    ]);
                }
            }
        }
    }
}
