<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildActivity extends Model
{
    use HasFactory;

    protected $primaryKey = 'activity_id';

    protected $fillable = [
        'assignment_id',
        'activity_type',
        'details',
        'activity_time',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'activity_time' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(
            CaregiverAssignment::class,
            'assignment_id',
            'assignment_id'
        );
    }
}
