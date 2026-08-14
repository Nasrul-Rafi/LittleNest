<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $primaryKey = 'booking_id';

    protected $fillable = [
        'booking_reference',
        'child_id',
        'service_id',
        'slot_id',
        'booking_date',
        'booking_time',
        'special_instructions',
        'status',
        'total_amount',
    ];

    protected static function booted(): void
    {
        static::created(function (Booking $booking) {
            if ($booking->booking_reference) {
                return;
            }

            $year = $booking->booking_date
                ? $booking->booking_date->format('Y')
                : now()->format('Y');

            $booking->booking_reference = 'LN-'
                . $year
                . '-'
                . str_pad(
                    (string) $booking->booking_id,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $booking->saveQuietly();
        });
    }

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(
            Child::class,
            'child_id',
            'child_id'
        );
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(
            Service::class,
            'service_id',
            'service_id'
        );
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(
            TimeSlot::class,
            'slot_id',
            'slot_id'
        );
    }

    public function getDisplayReferenceAttribute(): string
    {
        return $this->booking_reference
            ?: '#' . $this->booking_id;
    }

    public function caregiverAssignment(): HasOne
    {
        return $this->hasOne(
            CaregiverAssignment::class,
            'booking_id',
            'booking_id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class,
            'booking_id',
            'booking_id'
        );
    }

    public function bookingRequests(): HasMany
    {
        return $this->hasMany(
            BookingRequest::class,
            'booking_id',
            'booking_id'
        );
    }
}
