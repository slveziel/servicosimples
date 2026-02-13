<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asaas_customer_id',
        'asaas_subscription_id',
        'status',
        'price',
        'current_period_end',
        'cancelled_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'current_period_end' => 'date',
        'cancelled_at' => 'datetime',
    ];

    const STATUS = [
        'pending_payment' => 'pending_payment',
        'active' => 'active',
        'suspended' => 'suspended',
        'cancelled' => 'cancelled',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS['active'];
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS['pending_payment'];
    }
}
