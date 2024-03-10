<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class PatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'phone' => 'nullable|string|min:1|max:255',
            'birthday' => 'required|date:Y-m-d',
            'gender' => 'required|boolean',
            'sampling_date' => 'required|date_format:Y-m-d H:i',
            'sample_receipt_date' => 'required|date_format:Y-m-d H:i',
            'anamnes' => 'nullable|string',
            'categories' => 'required|array',
            'categories.*.code' => 'required|string|min:2|max:5',
            'categories.*.biopsyCustom' => 'required|bool',
            'categories.*.biopsyCustomValue' => 'required_without:categories.*.biopsy|max:255',
            'categories.*.biopsy' => 'required_without:categories.*.biopsyCustomValue|max:255',
            'categories.*.description' => 'required|string|min:2|max:255',
            'photos' => 'nullable|array',
            'photos.*' => 'required|mimes:jpg,jpeg,png|max:200000',
            'created_by' => 'required|integer',
            'location_id' => 'nullable|exists:locations,id',
            'medical_clinic_id' => 'required|exists:medical_clinics,id',
            'doctor_id' => 'required|exists:doctors,id',
        ];
    }

    protected function prepareForValidation()
    {
        $fields = [
            'gender' => $this->gender == 1,
            'birthday' => $this->birthday ? Carbon::parse($this->birthday)->format('Y-m-d') : null,
            'sampling_date' => $this->sampling_date ? Carbon::parse($this->sampling_date)->format('Y-m-d H:i') : null,
            'sample_receipt_date' => $this->sample_receipt_date ? Carbon::parse($this->sample_receipt_date)->format('Y-m-d H:i') : null,
        ];

        if ($this->isMethod('post')) {
            $fields['created_by'] = auth()->id();
        }

        if (! $this->user()?->can('select_doctor_patients')) {
            $fields['doctor_id'] = auth()->user()->doctor_id;
        }

        return $this->merge($fields);
    }

    public function messages(): array
    {
        return [
            'categories.*.biopsy.required_without' => 'Обязательно для заполнения',
            'categories.*.biopsyCustomValue.required_without' => 'Обязательно для заполнения',
        ];
    }
}
