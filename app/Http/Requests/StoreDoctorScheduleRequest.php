<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'practice_date' => ['required', 'date'], //Format: YYYY-MM-DD
            'start_time'    => ['required', 'date_format:H:i'], // Format: HH:MM
            'end_time'      => ['required', 'date_format:H:i', 'after:start_time'],
            'is_available'  => ['nullable', 'boolean'],
        ];
    }
}
