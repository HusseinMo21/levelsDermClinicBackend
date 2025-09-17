<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'priority',
        'is_read',
        'metadata',
        'related_user_id',
        'related_item_id',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    public function relatedItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'related_item_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Static methods for creating notifications
    public static function createWithdrawalNotification($itemName, $quantity, $doctorName)
    {
        return self::create([
            'title' => 'سحب أداة',
            'message' => "تم سحب {$quantity} وحدة من {$itemName} بواسطة {$doctorName}",
            'type' => 'withdrawal',
            'priority' => 'medium',
        ]);
    }

    public static function createSupplyRequestNotification()
    {
        return self::create([
            'title' => 'طلب توريد جديد',
            'message' => 'طلب توريد جديد قيد الانتظار',
            'type' => 'supply_request',
            'priority' => 'high',
        ]);
    }

    public static function createLowStockNotification($itemName, $currentStock)
    {
        return self::create([
            'title' => 'مخزون منخفض',
            'message' => "أداة {$itemName} على وشك النفاد - المخزون الحالي: {$currentStock}",
            'type' => 'low_stock',
            'priority' => 'urgent',
        ]);
    }
}