<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    use HasFactory;

    protected $primaryKey = 'child_id';

    protected $fillable = [
        'parent_profile_id',
        'full_name',
        'date_of_birth',
        'gender',
        'guardian_relation',
        'photo',
        'allergies',
        'medical_notes',
        'medicine_instructions',
        'special_needs',
        'emergency_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function parentProfile(): BelongsTo
    {
        return $this->belongsTo(
            ParentProfile::class,
            'parent_profile_id',
            'parent_profile_id'
        );
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(
            Booking::class,
            'child_id',
            'child_id'
        );
    }
}