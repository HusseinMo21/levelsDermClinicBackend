<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'description',
        'category',
        'subcategory',
        'unit_of_measure',
        'unit_cost',
        'minimum_stock_level',
        'maximum_stock_level',
        'has_expiry_date',
        'shelf_life_days',
        'requires_prescription',
        'storage_conditions',
        'usage_instructions',
        'contraindications',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'has_expiry_date' => 'boolean',
        'requires_prescription' => 'boolean',
        'contraindications' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function doctorRequests(): HasMany
    {
        return $this->hasMany(DoctorRequest::class);
    }

    // Accessors
    public function getCurrentStockAttribute(): int
    {
        return $this->batches()->where('status', 'active')->sum('quantity_remaining');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->minimum_stock_level;
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

    public function scopeLowStock($query)
    {
        return $query->whereHas('batches', function ($q) {
            $q->where('status', 'active');
        })->whereRaw('(
            SELECT SUM(quantity_remaining) 
            FROM inventory_batches 
            WHERE inventory_item_id = inventory_items.id AND status = 'active'
        ) <= minimum_stock_level');
    }
}
