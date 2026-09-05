<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VipCard extends Model
{
    protected $fillable = [
        'vip_client_id',
        'card_number',
        'qr_code',
        'card_type',
        'status',
        'issued_at',
        'expires_at',
        'suspended_at',
        'suspension_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(VipClient::class, 'vip_client_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CardSubscription::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
