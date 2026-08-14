<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Carbon $slot_date
 */
class TimeSlot extends Model
{
    use HasFactory;

    protected $primaryKey = 'slot_id';

    protected $fillable = [
        'service_id',
        'slot_date',
        'start_time',
        'end_time',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'slot_date' => 'date',
            'capacity' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(
            Service::class,
            'service_id',
            'service_id'
        );
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(
            Booking::class,
            'slot_id',
            'slot_id'
        );
    }

    public function activeBookingsCount(): int
    {
        if (array_key_exists('active_bookings_count', $this->attributes)) {
            return (int) $this->active_bookings_count;
        }

        return $this->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
    }

    public function remainingCapacity(): int
    {
        return max(
            0,
            $this->capacity - $this->activeBookingsCount()
        );
    }

    public function isBookable(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        if (!$this->service || $this->service->status !== 'active') {
            return false;
        }

        $slotDateTime = $this->slot_date->format('Y-m-d')
            . ' '
            . $this->start_time;

        if (strtotime($slotDateTime) <= now()->timestamp) {
            return false;
        }

        return $this->remainingCapacity() > 0;
    }
}
