<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'age'              => ['nullable', 'integer', 'min:0', 'max:120'],
            'gender'           => ['required', 'in:male,female'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'address'          => ['nullable', 'string', 'max:255'],
            'has_diabetes'     => ['nullable', 'boolean'],
            'has_hypertension' => ['nullable', 'boolean'],
            'has_allergy'      => ['nullable', 'boolean'],
            'allergy_details'  => ['nullable', 'string', 'max:255'],
            'is_pregnant'      => ['nullable', 'boolean'],
            'medical_notes'    => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'اسم المريض مطلوب.',
            'gender.required' => 'يجب تحديد الجنس.',
        ];
    }
}
