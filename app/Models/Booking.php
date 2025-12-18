<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'user_id',
        'hardware_type_id',
        'incharge_id',
        'device_brand',
        'device_model',
        'serial_number',
        'issue_description',
        'priority',
        'status',
        'diagnosis',
        'estimated_cost',
        'final_cost',
        'estimated_completion_date',
        'completed_at',
        'picked_up_at',
        'admin_notes',
        'incharge_notes',
        'images',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'final_cost' => 'decimal:2',
        'estimated_completion_date' => 'date',
        'completed_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'images' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = self::generateBookingNumber();
            }
        });
    }

    /**
     * Generate a unique booking number.
     */
    public static function generateBookingNumber(): string
    {
        $prefix = 'HRB';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Get the user who created the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the incharge assigned to this booking.
     */
    public function incharge()
    {
        return $this->belongsTo(User::class, 'incharge_id');
    }

    /**
     * Get the hardware type for this booking.
     */
    public function hardwareType()
    {
        return $this->belongsTo(HardwareType::class);
    }

    /**
     * Scope to get pending bookings.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get assigned bookings.
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    /**
     * Scope to get in-progress bookings.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to get completed bookings.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if booking can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'assigned']);
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'assigned' => 'blue',
            'in_progress' => 'indigo',
            'waiting_parts' => 'orange',
            'completed' => 'green',
            'cancelled' => 'red',
            'picked_up' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get priority color for UI.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray',
        };
    }
}

