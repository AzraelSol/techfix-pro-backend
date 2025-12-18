<?php

namespace App\Models;

use App\Events\NewNotificationEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'link',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Broadcast event when notification is created
        static::created(function ($notification) {
            broadcast(new NewNotificationEvent($notification));
        });
    }

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if notification is read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Create a notification for a user.
     */
    public static function notify(User $user, string $type, string $title, string $message, ?string $icon = null, ?string $link = null, ?array $data = null): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'link' => $link,
            'data' => $data,
        ]);
    }

    /**
     * Create notification for multiple users.
     */
    public static function notifyMany($users, string $type, string $title, string $message, ?string $icon = null, ?string $link = null, ?array $data = null): void
    {
        foreach ($users as $user) {
            self::notify($user, $type, $title, $message, $icon, $link, $data);
        }
    }
}
