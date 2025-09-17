<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
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
        'source',
        'source_details',
        'interested_services',
        'notes',
        'status',
        'priority',
        'last_contact_date',
        'next_follow_up_date',
        'assigned_to',
        'converted_to_patient',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_contact_date' => 'datetime',
        'next_follow_up_date' => 'datetime',
    ];

    // Relationships
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedToPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'converted_to_patient');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 3);
    }

    public function scopeNeedsFollowUp($query)
    {
        return $query->where('next_follow_up_date', '<=', now())
                    ->whereIn('status', ['new', 'contacted', 'qualified']);
    }
}
