<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\CardSubscription;
use App\Models\TravelAffiliate;
use App\Models\TravelRoute;
use App\Models\TripConsumption;
use App\Models\VipCard;
use App\Services\VipCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TripConsumptionController extends Controller
{
    public function __construct(private VipCardService $cards)
    {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $trips = TripConsumption::query()
            ->with(['client', 'card', 'travelRoute', 'departureAgency', 'subscription.package', 'travelAffiliate'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($q) => $q
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"))
                        ->orWhereHas('card', fn ($q) => $q->where('card_number', 'like', "%{$search}%"));
                });
            })
            ->latest('consumed_at')
            ->paginate(15)
            ->withQueryString();

        return view('backoffice.trips.index', compact('trips', 'search'));
    }

    public function create(?VipCard $vipCard = null): View
    {
        $cards = VipCard::query()
            ->with(['client', 'subscriptions' => fn ($q) => $q->where('status', 'active')->latest('starts_at')])
            ->where('status', 'active')
            ->whereHas('subscriptions', fn ($q) => $q->where('status', 'active')->where('trips_remaining', '>', 0))
            ->orderByDesc('id')
            ->get();

        $routes = TravelRoute::where('status', 'active')->orderBy('departure_city')->get();
        $agencies = Agency::where('status', 'active')->orderBy('city')->orderBy('name')->get();
        $affiliates = TravelAffiliate::query()
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get();

        return view('backoffice.trips.create', compact('vipCard', 'cards', 'routes', 'agencies', 'affiliates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vip_card_id' => ['required', 'integer', Rule::exists('vip_cards', 'id')->where('status', 'active')],
            'travel_route_id' => ['required', 'integer', Rule::exists('travel_routes', 'id')->where('status', 'active')],
            'departure_agency_id' => ['required', 'integer', Rule::exists('agencies', 'id')->where('status', 'active')],
            'travel_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'travel_affiliate_id' => ['nullable', 'integer', Rule::exists('travel_affiliates', 'id')->where('status', 'active')],
        ]);

        $card = VipCard::with('client')->findOrFail($data['vip_card_id']);
        $subscription = CardSubscription::query()
            ->where('vip_card_id', $card->id)
            ->where('status', 'active')
            ->latest('starts_at')
            ->first();

        if (! $subscription) {
            return back()
                ->withInput()
                ->withErrors(['vip_card_id' => 'Cette carte ne possede pas de forfait actif.']);
        }

        $route = TravelRoute::findOrFail($data['travel_route_id']);
        $affiliate = null;

        if (! empty($data['travel_affiliate_id'])) {
            $affiliate = TravelAffiliate::findOrFail($data['travel_affiliate_id']);
        }

        $this->cards->consumeTrip(
            $subscription,
            $route,
            (int) $data['departure_agency_id'],
            $request->user(),
            null,
            isset($data['travel_date']) ? \Illuminate\Support\Carbon::parse($data['travel_date']) : null,
            $data['notes'] ?? '',
            $affiliate
        );

        return redirect()->route('vip-cards.show', $card)
            ->with('status', 'Voyage enregistre. Solde mis a jour.');
    }
}
