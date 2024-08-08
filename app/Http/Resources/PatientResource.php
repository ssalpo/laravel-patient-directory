<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uniq_code' => $this->uniq_code,
            'name' => $this->name,
            'phone' => $this->phone,
            'case_numbers' => PatientCaseNumberResource::collection($this->whenLoaded('caseNumbers')),
            'case_numbers_joined' => $this->whenLoaded('caseNumbers', fn () => $this->caseNumbers->implode('formatted', ', ')),
            'birthday' => $this->birthday->format('d.m.Y'),
            'age' => now()->diff($this->birthday)->format('%y'),
            'gender' => $this->gender,
            'sampling_date' => $this->sampling_date?->format('d.m.Y H:i'),
            'sample_receipt_date' => $this->sample_receipt_date?->format('d.m.Y H:i'),
            'anamnes' => $this->anamnes,
            'doctor_id' => $this->doctor_id,
            'doctor' => DoctorResource::make($this->whenLoaded('doctor')),
            'medical_clinic_id' => $this->medical_clinic_id,
            'medical_clinic' => MedicalClinicResource::make($this->whenLoaded('medicalClinic')),
            'categories' => $this->categories,
            'categories_formatted' => implode(', ', $this->categories_formatted),
            'microscopic_description' => $this->microscopic_description,
            'diagnosis' => $this->diagnosis,
            'note' => $this->note,
            'note_text_count' => strlen(strip_tags($patient->note ?? '')),
            'comment' => $this->comment,
            'status' => $this->status,
            'photos' => PhotoResource::collection($this->whenLoaded('photos')),
            'hashid' => $this->hashid,
            'location' => LocationResource::make($this->whenLoaded('location')),
            'place_of_residence' => $this->place_of_residence,
            'print_date' => $this->print_date?->format('d.m.Y'),
            'created_at' => $this->created_at->format('d.m.Y'),
            'shared_to_id' => $this->shared_to_id,
        ];
    }
}
