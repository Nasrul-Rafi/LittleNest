<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ParentProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'parent_profile_id';

    protected $fillable = [
        'user_id',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            Child::class,
            'parent_profile_id',
            'parent_profile_id'
        );
    }

    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(
            Booking::class,
            Child::class,
            'parent_profile_id',
            'child_id',
            'parent_profile_id',
            'child_id'
        );
    }
}