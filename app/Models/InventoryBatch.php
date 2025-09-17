<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'batch_number',
        'supplier_batch_number',
        'quantity_received',
        'quantity_remaining',
        'quantity_used',
        'manufacturing_date',
        'expiry_date',
        'unit_cost',
        'total_cost',
        'status',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // Relationships
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function doctorRequests(): HasMany
    {
        return $this->hasMany(DoctorRequest::class);
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getDaysToExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->days_to_expiry !== null && $this->days_to_expiry <= 30;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    public function scopeByItem($query, $itemId)
    {
        return $query->where('inventory_item_id', $itemId);
    }
}
