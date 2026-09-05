<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardSubscription extends Model
{
    protected $fillable = [
        'vip_card_id',
        'package_id',
        'starts_at',
        'expires_at',
        'trips_total',
        'trips_remaining',
        'unit_price',
        'total_amount',
        'status',
        'renewed_from_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'trips_total' => 'integer',
            'trips_remaining' => 'integer',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(VipCard::class, 'vip_card_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'renewed_from_id');
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(TripConsumption::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
