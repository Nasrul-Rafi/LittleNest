<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaregiverProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'caregiver_profile_id';

    protected $fillable = [
        'user_id',
        'qualification',
        'experience_years',
        'specialization',
        'skills',
        'bio',
        'availability_status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
