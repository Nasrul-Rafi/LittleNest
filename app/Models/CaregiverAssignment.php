<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaregiverAssignment extends Model
{
    use HasFactory;

    protected $primaryKey = 'assignment_id';

    protected $fillable = [
        'booking_id',
        'caregiver_id',
        'assigned_by',
        'assigned_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(
            Booking::class,
            'booking_id',
            'booking_id'
        );
    }

    public function caregiver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'caregiver_id',
            'id'
        );
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_by',
            'id'
        );
    }

    public function activities(): HasMany
    {
        return $this->hasMany(
            ChildActivity::class,
            'assignment_id',
            'assignment_id'
        );
    }
}
