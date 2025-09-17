<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'national_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_2',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_history',
        'allergies',
        'current_medications',
        'status',
        'visit_count',
        'loyalty_points',
        'last_loyalty_points_used',
        'last_activity',
        'first_visit_date',
        'notes',
        'profile_image',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_loyalty_points_used' => 'datetime',
        'last_activity' => 'datetime',
        'first_visit_date' => 'datetime',
    ];

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'patient_services')
                    ->withPivot(['service_date', 'price_paid', 'notes', 'status', 'created_by'])
                    ->withTimestamps();
    }

    public function patientServices(): HasMany
    {
        return $this->hasMany(PatientService::class);
    }

    public function lead(): HasMany
    {
        return $this->hasMany(Lead::class, 'converted_to_patient');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }
}
