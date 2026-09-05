<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelRoute extends Model
{
    protected $fillable = [
        'departure_city',
        'arrival_city',
        'distance_km',
        'estimated_duration',
        'status',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }
}
