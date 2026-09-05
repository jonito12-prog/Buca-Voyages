<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TripConsumption;
use App\Services\ClientAccountService;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __construct(private ClientAccountService $accounts)
    {
    }

    public function __invoke(): View
    {
        $user = auth()->user();
        $vipClient = $this->accounts->resolveVipClient($user);

        if (! $vipClient) {
            return view('client.portal', [
                'user' => $user,
                'linked' => false,
            ]);
        }

        $vipClient->load(['cards' => fn ($query) => $query
            ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'suspended' THEN 2 ELSE 3 END")
            ->orderByDesc('id')]);

        $card = $vipClient->cards->first();

        if ($card) {
            $card->load([
                'subscriptions' => fn ($query) => $query->latest('starts_at'),
                'subscriptions.package',
                'subscriptions.payments',
            ]);
        }

        $currentSubscription = $card?->subscriptions->first();

        $consumptions = TripConsumption::query()
            ->where('vip_client_id', $vipClient->id)
            ->with(['travelRoute', 'departureAgency', 'card', 'travelAffiliate'])
            ->latest('consumed_at')
            ->limit(25)
            ->get();

        $affiliates = $vipClient->travelAffiliates()->with('card')->latest('id')->get();

        $sentParcels = $vipClient->parcels()
            ->with(['originAgency', 'destinationAgency'])
            ->latest('id')
            ->get();

        $receivedParcels = \App\Models\Parcel::query()
            ->where('receiver_phone', $vipClient->phone)
            ->with(['originAgency', 'destinationAgency'])
            ->latest('id')
            ->get();

        return view('client.portal', [
            'user' => $user,
            'linked' => true,
            'vipClient' => $vipClient,
            'card' => $card,
            'currentSubscription' => $currentSubscription,
            'consumptions' => $consumptions,
            'affiliates' => $affiliates,
            'sentParcels' => $sentParcels,
            'receivedParcels' => $receivedParcels,
        ]);
    }
}
