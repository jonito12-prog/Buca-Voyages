@extends('layouts.app', ['title' => 'Carte VIP - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content">
        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
        @if ($currentSubscription?->status === 'pending_payment')
            @php($pendingPayment = $currentSubscription->payments->firstWhere('payment_status', 'pending'))
            <div class="alert warn">Paiement en attente de confirmation. Le forfait ne sera utilisable qu'apres validation.@if ($pendingPayment) <a href="{{ route('payments.show', $pendingPayment) }}">Confirmer le paiement</a>@endif</div>
        @endif

        <div class="page-head">
            <div>
                <p class="section-tag">Carte VIP</p>
                <h1>{{ $vipCard->card_number }}</h1>
                <span class="status {{ $vipCard->status }}">{{ ['active' => 'Active', 'suspended' => 'Suspendue', 'expired' => 'Expiree'][$vipCard->status] ?? $vipCard->status }}</span>
            </div>
            <div class="head-actions">
                @if ($vipCard->status === 'active' && ($currentSubscription?->trips_remaining ?? 0) > 0 && $currentSubscription?->status === 'active')
                    <a class="command secondary-link" href="{{ route('trips.create-for-card', $vipCard) }}">Enregistrer un voyage</a>
                @endif
                <a class="command primary-link" href="{{ route('vip-cards.subscription', $vipCard) }}">{{ $currentSubscription ? 'Renouveler le forfait' : 'Attribuer un forfait' }}</a>
            </div>
        </div>

        <div class="metric-grid">
            <section class="metric important"><span>Voyages restants</span><strong>{{ $currentSubscription?->trips_remaining ?? 0 }}</strong></section>
            <section class="metric"><span>Voyages achetes</span><strong>{{ $currentSubscription?->trips_total ?? 0 }}</strong></section>
            <section class="metric"><span>Fin de validite</span><strong class="date-value">{{ $currentSubscription?->expires_at?->format('d/m/Y') ?? '-' }}</strong></section>
        </div>

        <div class="detail-grid card-details">
            <section class="detail-panel">
                <h2>Client titulaire</h2>
                <dl><dt>Nom</dt><dd>{{ $vipCard->client->first_name }} {{ $vipCard->client->last_name }}</dd><dt>Telephone</dt><dd>{{ $vipCard->client->phone }}</dd><dt>Date de creation</dt><dd>{{ $vipCard->created_at?->format('d/m/Y H:i') ?: '-' }}</dd></dl>
            </section>
            <section class="detail-panel">
                <h2>Gestion du statut</h2>
                @if ($vipCard->status === 'suspended')
                    <p class="reason">Raison : {{ $vipCard->suspension_reason }}</p>
                    <form method="POST" action="{{ route('vip-cards.reactivate', $vipCard) }}">@csrf @method('PATCH')<button class="filter-button" type="submit">Reactiver la carte</button></form>
                @else
                    <form method="POST" action="{{ route('vip-cards.suspend', $vipCard) }}">@csrf @method('PATCH')
                        <label><span>Raison de suspension</span><input type="text" name="reason" required placeholder="Ex. verification administrative"></label>
                        <button class="danger-action status-action" type="submit">Suspendre la carte</button>
                    </form>
                @endif
            </section>
        </div>

        <section class="data-panel section-panel">
            <div class="panel-title"><h2>Historique des forfaits</h2><span>{{ $vipCard->subscriptions->count() }} operation(s)</span></div>
            @if ($vipCard->subscriptions->isEmpty())
                <p class="empty-block">Aucun forfait attribue a cette carte.</p>
            @else
                <table class="data-table">
                    <thead><tr><th>Forfait</th><th>Solde</th><th>Validite</th><th>Paiement</th><th>Statut</th></tr></thead>
                    <tbody>
                        @foreach ($vipCard->subscriptions as $subscription)
                            @php($payment = $subscription->payments->first())
                            <tr>
                                <td><strong>{{ $subscription->package->name }}</strong><small>{{ number_format($subscription->total_amount, 0, ',', ' ') }} FCFA</small></td>
                                <td>{{ $subscription->trips_remaining }} / {{ $subscription->trips_total }}</td>
                                <td>{{ $subscription->starts_at->format('d/m/Y') }} - {{ $subscription->expires_at->format('d/m/Y') }}</td>
                                <td>{{ $payment?->payment_status === 'paid' ? 'Paye' : 'En attente' }}<small>{{ $payment?->transaction_reference }}</small></td>
                                <td><span class="status {{ $subscription->status }}">{{ ['active' => 'Actif', 'pending_payment' => 'Paiement en attente', 'renewed' => 'Renouvele', 'expired' => 'Expire', 'completed' => 'Epuise'][$subscription->status] ?? $subscription->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="data-panel section-panel">
            <div class="panel-title"><h2>Historique des voyages consommes</h2><span>{{ $consumptions->count() }} voyage(s)</span></div>
            @if ($consumptions->isEmpty())
                <p class="empty-block">Aucun voyage consomme sur cette carte.</p>
            @else
                <table class="data-table">
                    <thead><tr><th>Reference</th><th>Voyageur</th><th>Trajet</th><th>Agence</th><th>Date voyage</th><th>Debite</th></tr></thead>
                    <tbody>
                        @foreach ($consumptions as $consumption)
                            <tr>
                                <td><strong>{{ $consumption->reference }}</strong><small>{{ $consumption->consumed_at->format('d/m/Y H:i') }}</small></td>
                                <td>{{ $consumption->travelAffiliate?->fullName() ?? 'Titulaire' }}</td>
                                <td>{{ $consumption->travelRoute->departure_city }} → {{ $consumption->travelRoute->arrival_city }}</td>
                                <td>{{ $consumption->departureAgency?->name ?? '-' }}</td>
                                <td>{{ $consumption->travel_date->format('d/m/Y') }}</td>
                                <td>{{ $consumption->trips_debited }} voyage(s)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </main>
</div>
@endsection
