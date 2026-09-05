<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripConsumption extends Model
{
    protected $fillable = [
        'card_subscription_id',
        'vip_card_id',
        'vip_client_id',
        'travel_affiliate_id',
        'travel_route_id',
        'departure_agency_id',
        'arrival_agency_id',
        'travel_date',
        'consumed_at',
        'consumed_by',
        'trips_debited',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'travel_date' => 'date',
            'consumed_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CardSubscription::class, 'card_subscription_id');
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(VipCard::class, 'vip_card_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(VipClient::class, 'vip_client_id');
    }

    public function travelAffiliate(): BelongsTo
    {
        return $this->belongsTo(TravelAffiliate::class);
    }

    public function travelRoute(): BelongsTo
    {
        return $this->belongsTo(TravelRoute::class);
    }

    public function departureAgency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'departure_agency_id');
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumed_by');
    }
}
