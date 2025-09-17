<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'priority',
        'status',
        'read_at',
        'scheduled_at',
        'is_system_notification',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'is_system_notification' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system_notification', true);
    }

    public function scopeScheduled($query)
    {
        return $query->where('scheduled_at', '<=', now())
                    ->where('status', 'unread');
    }

    // Methods
    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    public function markAsUnread(): void
    {
        $this->update([
            'status' => 'unread',
            'read_at' => null,
        ]);
    }
}
