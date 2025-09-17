<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ToolWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'withdrawal_number',
        'inventory_item_id',
        'doctor_id',
        'appointment_id',
        'quantity',
        'operation_name',
        'notes',
        'status',
        'withdrawn_by',
        'withdrawal_date',
    ];

    protected $casts = [
        'withdrawal_date' => 'datetime',
    ];

    // Relationships
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function withdrawnBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'withdrawn_by');
    }

    // Accessors
    public function getDoctorNameAttribute(): string
    {
        return $this->doctor->full_name ?? 'غير محدد';
    }

    public function getToolNameAttribute(): string
    {
        return $this->inventoryItem->name ?? 'غير محدد';
    }

    // Scopes
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('withdrawal_date', '>=', now()->subDays($days));
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('withdrawal_date', $date);
    }
}