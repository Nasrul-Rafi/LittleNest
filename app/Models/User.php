<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{

    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function parentProfile(): HasOne
    {
        return $this->hasOne(
            ParentProfile::class,
            'user_id',
            'id'
        );
    }

    public function caregiverProfile(): HasOne
    {
        return $this->hasOne(
            CaregiverProfile::class,
            'user_id',
            'id'
        );
    }

    public function caregiverAssignments(): HasMany
    {
        return $this->hasMany(
            CaregiverAssignment::class,
            'caregiver_id',
            'id'
        );
    }
}
