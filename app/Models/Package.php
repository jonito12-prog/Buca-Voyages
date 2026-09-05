<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'name',
        'period_type',
        'trip_count',
        'validity_days',
        'price',
        'travel_route_id',
        'class_type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'trip_count' => 'integer',
            'validity_days' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function travelRoute(): BelongsTo
    {
        return $this->belongsTo(TravelRoute::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CardSubscription::class);
    }
}
