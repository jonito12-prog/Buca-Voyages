<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parcel extends Model
{
    protected $fillable = [
        'sender_vip_client_id',
        'sender_name',
        'sender_phone',
        'sender_email',
        'receiver_name',
        'receiver_phone',
        'receiver_identity_number',
        'origin_agency_id',
        'destination_agency_id',
        'tracking_code',
        'size_category',
        'declared_value',
        'shipping_price',
        'payment_method',
        'payment_status',
        'status',
        'notes',
        'created_by',
        'registered_at',
        'dispatched_at',
        'arrived_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'declared_value' => 'decimal:2',
            'shipping_price' => 'decimal:2',
            'registered_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'arrived_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(VipClient::class, 'sender_vip_client_id');
    }

    public function originAgency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'origin_agency_id');
    }

    public function destinationAgency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'destination_agency_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
