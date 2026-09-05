<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VipClient extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'phone',
        'email',
        'address',
        'identity_type',
        'identity_number',
        'status',
        'created_by',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function cards(): HasMany
    {
        return $this->hasMany(VipCard::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function travelAffiliates(): HasMany
    {
        return $this->hasMany(TravelAffiliate::class);
    }

    public function activeTravelAffiliates(): HasMany
    {
        return $this->hasMany(TravelAffiliate::class)->where('status', 'active');
    }

    public function parcels(): HasMany
    {
        return $this->hasMany(Parcel::class, 'sender_vip_client_id');
    }

    public function tripConsumptions(): HasMany
    {
        return $this->hasMany(TripConsumption::class);
    }
}
