<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_status',
        'paid_at',
        'refund_amount',
        'refunded_at',
        'refund_note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'refund_amount' => 'decimal:2',
            'refunded_at' => 'datetime',
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

    public function getDisplayMethodAttribute(): string
    {
        if ($this->payment_method === 'mobile-banking') {
            return 'Mobile Banking (Demo)';
        }

        return ucwords(str_replace('-', ' ', $this->payment_method));
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->refunded_at) {
            return 'refunded';
        }

        return $this->payment_status;
    }

    public function isRefunded(): bool
    {
        return $this->refunded_at !== null;
    }
}
