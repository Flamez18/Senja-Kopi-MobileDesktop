<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'order_number',
        'order_status',
        'payment_status',
        'payment_method',
        'notes',
        'subtotal',
        'service_fee',
        'total',
        'paid_at',
        'midtrans_snap_token',
        'midtrans_transaction_id',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'service_fee' => 'integer',
        'total' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
