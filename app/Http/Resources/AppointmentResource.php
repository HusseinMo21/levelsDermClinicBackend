<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
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
            'appointment_id' => $this->appointment_id,
            'operation_number' => $this->operation_number,
            'patient_name' => $this->whenLoaded('patient', function () {
                return $this->patient->full_name;
            }),
            'patient_id' => $this->patient_id,
            'doctor_name' => $this->whenLoaded('doctor', function () {
                return $this->doctor->user->name;
            }),
            'doctor_id' => $this->doctor_id,
            'service_name' => $this->whenLoaded('service', function () {
                return $this->service->name;
            }),
            'service_id' => $this->service_id,
            'appointment_date' => $this->appointment_date?->format('Y-m-d'),
            'appointment_time' => $this->appointment_date?->format('H:i'),
            'end_time' => $this->end_time?->format('H:i'),
            'status' => $this->status,
            'type' => $this->type,
            'notes' => $this->notes,
            'diagnosis' => $this->diagnosis,
            'treatment_plan' => $this->treatment_plan,
            'prescription' => $this->prescription,
            'before_photos' => $this->before_photos,
            'after_photos' => $this->after_photos,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'net_amount' => $this->total_amount - $this->discount_amount,
            'payment_required' => $this->payment_required,
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'created_by_name' => $this->whenLoaded('createdBy', function () {
                return $this->createdBy->name;
            }),
            'updated_by_name' => $this->whenLoaded('updatedBy', function () {
                return $this->updatedBy->name;
            }),
        ];
    }
}
