<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'card_subscription_id',
        'vip_client_id',
        'amount',
        'payment_method',
        'payment_status',
        'transaction_reference',
        'paid_at',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CardSubscription::class, 'card_subscription_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(VipClient::class, 'vip_client_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
