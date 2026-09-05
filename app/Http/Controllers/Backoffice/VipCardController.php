<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Package;
use App\Models\Payment;
use App\Models\VipCard;
use App\Models\VipClient;
use App\Services\VipCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VipCardController extends Controller
{
    public function __construct(private VipCardService $cards)
    {
    }

    public function create(VipClient $vipClient): View|RedirectResponse
    {
        if ($vipClient->status !== 'active') {
            return redirect()->route('vip-clients.show', $vipClient)
                ->withErrors(['card' => 'Une carte peut etre creee uniquement pour un client actif.']);
        }

        return view('backoffice.cards.create', compact('vipClient'));
    }

    public function store(Request $request, VipClient $vipClient): RedirectResponse
    {
        if ($vipClient->status !== 'active') {
            throw ValidationException::withMessages(['card' => 'Une carte peut etre creee uniquement pour un client actif.']);
        }

        if ($vipClient->cards()->whereIn('status', ['active', 'suspended'])->exists()) {
            throw ValidationException::withMessages([
                'card' => 'Ce client possede deja une carte en cours. Consultez-la pour la renouveler ou la suspendre.',
            ]);
        }

        $data = $request->validate([
            'card_type' => ['required', Rule::in(['vip'])],
        ]);

        $card = VipCard::create([
            'vip_client_id' => $vipClient->id,
            'card_number' => $this->newCardNumber(),
            'card_type' => $data['card_type'],
            'status' => 'active',
            'issued_at' => now(),
            'created_by' => $request->user()->id,
        ]);

        $this->audit($request, 'vip_card.created', $card, [
            'card_number' => $card->card_number,
            'client_id' => $vipClient->id,
        ]);

        return redirect()->route('vip-cards.show', $card)
            ->with('status', 'Carte VIP creee. Attribuez maintenant un forfait de voyages.');
    }

    public function show(VipCard $vipCard): View
    {
        $this->synchronizeExpiration($vipCard);

        $vipCard->load([
            'client',
            'creator',
            'subscriptions' => fn ($query) => $query->latest('starts_at'),
            'subscriptions.package',
            'subscriptions.payments',
        ]);

        $consumptions = $vipCard->subscriptions()
            ->with(['consumptions' => fn ($query) => $query
                ->with(['travelRoute', 'departureAgency', 'consumer', 'travelAffiliate'])
                ->latest('consumed_at')])
            ->get()
            ->pluck('consumptions')
            ->flatten()
            ->sortByDesc('consumed_at')
            ->values();

        $currentSubscription = $vipCard->subscriptions->first();

        return view('backoffice.cards.show', compact('vipCard', 'currentSubscription', 'consumptions'));
    }

    public function subscriptionForm(VipCard $vipCard): View|RedirectResponse
    {
        $vipCard->load('client');

        if ($vipCard->client->status !== 'active') {
            return redirect()->route('vip-cards.show', $vipCard)
                ->withErrors(['card' => 'Un forfait peut etre attribue uniquement a un client actif.']);
        }

        $packages = Package::where('status', 'active')->orderBy('price')->get();
        $currentSubscription = $vipCard->subscriptions()
            ->where('status', 'active')
            ->latest('starts_at')
            ->first();

        return view('backoffice.cards.subscription', compact('vipCard', 'packages', 'currentSubscription'));
    }

    public function subscribe(Request $request, VipCard $vipCard): RedirectResponse
    {
        $vipCard->load('client');

        if ($vipCard->client->status !== 'active') {
            throw ValidationException::withMessages(['card' => 'Un forfait peut etre attribue uniquement a un client actif.']);
        }

        if ($vipCard->subscriptions()->where('status', 'pending_payment')->exists()) {
            throw ValidationException::withMessages([
                'payment' => 'Un paiement est deja en attente de confirmation sur cette carte.',
            ]);
        }

        $data = $request->validate([
            'package_id' => ['required', 'integer', Rule::exists('packages', 'id')->where('status', 'active')],
            'payment_method' => ['required', Rule::in(['cash', 'mobile_money', 'bank_transfer'])],
            'payment_status' => ['required', Rule::in(['paid', 'pending'])],
            'transaction_reference' => ['nullable', 'string', 'max:255', Rule::unique('payments', 'transaction_reference')],
        ]);

        $renewedFrom = $vipCard->subscriptions()
            ->where('status', 'active')
            ->latest('starts_at')
            ->first();

        if ($renewedFrom && $renewedFrom->trips_remaining > 0 && ! $request->boolean('confirm_replacement')) {
            throw ValidationException::withMessages([
                'confirm_replacement' => 'Confirmez le renouvellement : le forfait actif contient encore des voyages.',
            ]);
        }

        $package = Package::findOrFail($data['package_id']);
        $reference = trim((string) ($data['transaction_reference'] ?? ''));
        $reference = $reference !== '' ? $reference : $this->newPaymentReference();

        $this->cards->subscribe(
            $vipCard,
            $package,
            $request->user(),
            $data['payment_method'],
            $data['payment_status'],
            $reference,
            $renewedFrom
        );

        return redirect()->route('vip-cards.show', $vipCard)
            ->with('status', $renewedFrom ? 'Forfait renouvele avec succes.' : 'Forfait attribue avec succes.');
    }

    public function suspend(Request $request, VipCard $vipCard): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $this->cards->suspendCard($vipCard, $request->user(), $data['reason']);

        return redirect()->route('vip-cards.show', $vipCard)->with('status', 'Carte suspendue.');
    }

    public function reactivate(Request $request, VipCard $vipCard): RedirectResponse
    {
        $this->cards->reactivateCard($vipCard, $request->user());

        return redirect()->route('vip-cards.show', $vipCard)->with('status', 'Carte reactivee.');
    }

    private function newCardNumber(): string
    {
        do {
            $number = 'BUCA-VIP-' . now()->format('ym') . '-' . random_int(100000, 999999);
        } while (VipCard::where('card_number', $number)->exists());

        return $number;
    }

    private function newPaymentReference(): string
    {
        do {
            $reference = 'PAY-' . now()->format('YmdHis') . '-' . random_int(100, 999);
        } while (Payment::where('transaction_reference', $reference)->exists());

        return $reference;
    }

    private function synchronizeExpiration(VipCard $card): void
    {
        $subscription = $card->subscriptions()
            ->where('status', 'active')
            ->latest('starts_at')
            ->first();

        if ($subscription && $subscription->expires_at->isPast()) {
            $subscription->update(['status' => 'expired']);
            $card->update(['status' => 'expired']);
        }
    }

    private function audit(Request $request, string $action, VipCard $card, array $values): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'module' => 'vip_cards',
            'entity_type' => VipCard::class,
            'entity_id' => $card->id,
            'new_values' => $values,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }
}
