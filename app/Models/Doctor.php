<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'license_number',
        'specialization',
        'qualifications',
        'experience_years',
        'consultation_fee',
        'bio',
        'profile_image',
        'working_hours',
        'available_days',
        'status',
        'notes',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'available_days' => 'array',
        'consultation_fee' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function doctorRequests(): HasMany
    {
        return $this->hasMany(DoctorRequest::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? 'Unknown';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySpecialization($query, $specialization)
    {
        return $query->where('specialization', $specialization);
    }

    public function scopeAvailable($query, $date = null)
    {
        $date = $date ?? now();
        $dayOfWeek = strtolower($date->format('l'));
        
        return $query->where('status', 'active')
                    ->whereJsonContains('available_days', $dayOfWeek);
    }
}
