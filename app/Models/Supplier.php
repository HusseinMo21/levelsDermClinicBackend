<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_code',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'phone_2',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_id',
        'website',
        'payment_terms',
        'credit_limit',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByPaymentTerms($query, $terms)
    {
        return $query->where('payment_terms', $terms);
    }
}
