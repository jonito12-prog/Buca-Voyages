<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Payment;
use App\Models\VipCard;
use App\Services\ClientAccountService;
use App\Services\VipCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private ClientAccountService $accounts,
        private VipCardService $cards
    ) {
    }

    public function subscriptionForm(): View|RedirectResponse
    {
        $user = auth()->user();
        $vipClient = $this->accounts->resolveVipClient($user);

        if (!$vipClient) {
            return redirect()->route('client.portal')
                ->withErrors(['subscription' => 'Votre compte n\'est pas encore lié à une fiche client VIP.']);
        }

        if ($vipClient->status !== 'active') {
            return redirect()->route('client.portal')
                ->withErrors(['subscription' => 'Votre compte client VIP n\'est pas actif.']);
        }

        $vipClient->load('cards');
        $card = $vipClient->cards->whereIn('status', ['active', 'suspended', 'expired'])->first();

        if (!$card) {
            return redirect()->route('client.portal')
                ->withErrors(['subscription' => 'Aucune carte VIP n\'est associée à votre compte. Contactez une agence.']);
        }

        if ($card->status === 'suspended') {
            return redirect()->route('client.portal')
                ->withErrors(['subscription' => 'Votre carte VIP est suspendue.']);
        }

        $packages = Package::where('status', 'active')->orderBy('price')->get();
        
        $currentSubscription = $card->subscriptions()
            ->where('status', 'active')
            ->latest('starts_at')
            ->first();

        return view('client.subscription', compact('card', 'packages', 'currentSubscription'));
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $vipClient = $this->accounts->resolveVipClient($user);

        if (!$vipClient || $vipClient->status !== 'active') {
            throw ValidationException::withMessages(['subscription' => 'Action non autorisée.']);
        }

        $vipClient->load('cards');
        $card = $vipClient->cards->whereIn('status', ['active', 'suspended', 'expired'])->first();

        if (!$card || $card->status === 'suspended') {
            throw ValidationException::withMessages(['subscription' => 'Carte VIP invalide ou suspendue.']);
        }

        if ($card->subscriptions()->where('status', 'pending_payment')->exists()) {
            throw ValidationException::withMessages([
                'payment' => 'Un paiement est déjà en attente de confirmation pour votre carte.',
            ]);
        }

        $data = $request->validate([
            'package_id' => ['required', 'integer', Rule::exists('packages', 'id')->where('status', 'active')],
            'payment_method' => ['required', Rule::in(['mobile_money'])],
            'transaction_reference' => ['nullable', 'string', 'max:255', Rule::unique('payments', 'transaction_reference')],
        ]);

        $renewedFrom = $card->subscriptions()
            ->where('status', 'active')
            ->latest('starts_at')
            ->first();

        if ($renewedFrom && $renewedFrom->trips_remaining > 0 && !$request->boolean('confirm_replacement')) {
            throw ValidationException::withMessages([
                'confirm_replacement' => 'Veuillez confirmer le remplacement du forfait actif qui contient encore des voyages.',
            ]);
        }

        $package = Package::findOrFail($data['package_id']);
        
        // Auto-generate reference if empty
        $reference = trim((string) ($data['transaction_reference'] ?? ''));
        if ($reference === '') {
            do {
                $reference = 'PAY-MOMO-' . now()->format('YmdHis') . '-' . random_int(100, 999);
            } while (Payment::where('transaction_reference', $reference)->exists());
        }

        $this->cards->subscribe(
            $card,
            $package,
            $user,
            $data['payment_method'],
            'pending', // Starts as pending until validated by agent
            $reference,
            $renewedFrom
        );

        return redirect()->route('client.portal')
            ->with('status', 'Votre demande de forfait a été enregistrée avec succès. Veuillez valider le paiement Mobile Money ou vous présenter en agence.');
    }
}
