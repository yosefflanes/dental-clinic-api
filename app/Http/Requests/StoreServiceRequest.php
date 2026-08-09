<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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

        // Jika methodnya PUT/PATCH (Update)
        if ($this->isMethod('put') || $this->isMethod('patch')){
            return [
                'name'          => ['sometimes', 'required', 'string', 'max:255'],
                'description'   => ['nullable', 'string'],
                'price'         => ['sometimes', 'required', 'numeric', 'min:0'],
                'is_active'     => ['sometimes', 'required', 'boolean'],
            ];
        }

        // Jika methodnya POST (Create)
        return [
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'price'         => ['required', 'numeric', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
        ];
    }
}
