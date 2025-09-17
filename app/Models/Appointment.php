<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'operation_number',
        'patient_id',
        'doctor_id',
        'service_id',
        'appointment_date',
        'end_time',
        'status',
        'type',
        'notes',
        'diagnosis',
        'treatment_plan',
        'prescription',
        'before_photos',
        'after_photos',
        'total_amount',
        'discount_amount',
        'payment_required',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'end_time' => 'datetime',
        'before_photos' => 'array',
        'after_photos' => 'array',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'payment_required' => 'boolean',
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
