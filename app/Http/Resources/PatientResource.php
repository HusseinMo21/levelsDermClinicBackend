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
            'patient_id' => $this->patient_id,
            'national_id' => $this->national_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_2' => $this->phone_2,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'age' => $this->age ?? null,
            'gender' => $this->gender,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'medical_history' => $this->medical_history,
            'allergies' => $this->allergies,
            'current_medications' => $this->current_medications,
            'status' => $this->status,
            'visit_count' => $this->visit_count,
            'notes' => $this->notes,
            'profile_image' => $this->profile_image,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'created_by_name' => $this->whenLoaded('createdBy', function () {
                return $this->createdBy->name;
            }),
        ];
    }
}
