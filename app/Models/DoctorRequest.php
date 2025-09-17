<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'doctor_id',
        'inventory_item_id',
        'inventory_batch_id',
        'appointment_id',
        'quantity_requested',
        'quantity_provided',
        'purpose',
        'notes',
        'status',
        'requested_at',
        'fulfilled_at',
        'fulfilled_by',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    // Relationships
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', 'fulfilled');
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }
}
