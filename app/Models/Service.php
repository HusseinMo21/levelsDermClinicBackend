<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_code',
        'name',
        'description',
        'category',
        'subcategory',
        'specialization',
        'price',
        'duration_minutes',
        'requirements',
        'aftercare_instructions',
        'contraindications',
        'requires_consultation',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'contraindications' => 'array',
        'requires_consultation' => 'boolean',
        'is_active' => 'boolean',
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

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'patient_services')
                    ->withPivot(['service_date', 'price_paid', 'notes', 'status', 'created_by'])
                    ->withTimestamps();
    }

    public function patientServices(): HasMany
    {
        return $this->hasMany(PatientService::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeRequiresConsultation($query)
    {
        return $query->where('requires_consultation', true);
    }
}
