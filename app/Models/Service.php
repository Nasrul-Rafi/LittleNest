<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $primaryKey = 'service_id';

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(
            Booking::class,
            'service_id',
            'service_id'
        );
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(
            TimeSlot::class,
            'service_id',
            'service_id'
        );
    }
}