<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelAffiliate extends Model
{
    protected $fillable = [
        'vip_client_id',
        'vip_card_id',
        'first_name',
        'last_name',
        'phone',
        'relationship',
        'identity_type',
        'identity_number',
        'status',
        'notes',
        'created_by',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(VipClient::class, 'vip_client_id');
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(VipCard::class, 'vip_card_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(TripConsumption::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
