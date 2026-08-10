<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Child extends Model
{
    use HasFactory;

    protected $primaryKey = 'child_id';

    protected $fillable = [
        'parent_profile_id',
        'full_name',
        'date_of_birth',
        'gender',
        'photo',
        'allergies',
        'medical_notes',
        'special_needs',
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
}