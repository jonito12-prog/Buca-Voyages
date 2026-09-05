@extends('layouts.app', ['title' => 'Fiche client VIP - Buca Voyages VIP'])
@section('body')
<div class="shell">
    @include('layouts.navigation')
    <main class="office-content">
        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
        <div class="page-head">
            <div><p class="section-tag">Fiche client VIP</p><h1>{{ $vipClient->first_name }} {{ $vipClient->last_name }}</h1><span class="status {{ $vipClient->status }}">{{ $vipClient->status }}</span></div>
            <a class="command primary-link" href="{{ route('vip-clients.edit', $vipClient) }}">Modifier</a>
        </div>
        <div class="detail-grid">
            <section class="detail-panel"><h2>Coordonnees</h2><dl><dt>Telephone</dt><dd>{{ $vipClient->phone }}</dd><dt>Email</dt><dd>{{ $vipClient->email ?: 'Non renseigne' }}</dd><dt>Adresse</dt><dd>{{ $vipClient->address ?: 'Non renseignee' }}</dd><dt>Date de naissance</dt><dd>{{ $vipClient->birth_date?->format('d/m/Y') ?: 'Non renseignee' }}</dd></dl></section>
            <section class="detail-panel"><h2>Identification</h2><dl><dt>Type de piece</dt><dd>{{ $vipClient->identity_type ?: 'Non renseigne' }}</dd><dt>Numero</dt><dd>{{ $vipClient->identity_number ?: 'Non renseigne' }}</dd><dt>Cree par</dt><dd>{{ $vipClient->creator?->name ?: 'Systeme' }}</dd></dl></section>
        </div>
        <section class="data-panel section-panel"><div class="panel-title"><h2>Cartes associees</h2>@if ($vipClient->status === 'active' && ! $vipClient->cards->contains(fn ($card) => in_array($card->status, ['active', 'suspended'], true)))<a class="command primary-link compact" href="{{ route('vip-cards.create', $vipClient) }}">Creer une carte</a>@else<span>{{ $vipClient->cards->count() }} carte(s)</span>@endif</div>
            @if ($vipClient->cards->isEmpty())<p class="empty-block">Aucune carte n'est encore attribuee a ce client.</p>@else
            <table class="data-table"><thead><tr><th>Numero</th><th>Date de creation</th><th>Statut</th><th>Solde actuel</th><th></th></tr></thead><tbody>@foreach($vipClient->cards as $card) @php($subscription = $card->subscriptions->sortByDesc('starts_at')->first()) <tr><td><strong>{{ $card->card_number }}</strong><small>Carte VIP</small></td><td>{{ $card->created_at?->format('d/m/Y') ?: '-' }}<small>{{ $card->created_at?->format('H:i') ?: '' }}</small></td><td><span class="status {{ $card->status }}">{{ ['active' => 'Active', 'suspended' => 'Suspendue', 'expired' => 'Expiree', 'renewed' => 'Renouvelee'][$card->status] ?? $card->status }}</span></td><td>{{ $subscription ? $subscription->trips_remaining.' / '.$subscription->trips_total.' voyages' : 'Aucun forfait' }}</td><td class="actions"><a href="{{ route('vip-cards.show', $card) }}">Ouvrir</a></td></tr>@endforeach</tbody></table>@endif
        </section>
        @include('backoffice.clients._affiliates')

        <!-- Suivi des Colis du Client -->
        <section class="data-panel section-panel">
            <div class="panel-title">
                <h2>Envois de Colis</h2>
                <a class="command primary-link compact" href="{{ route('parcels.create', ['vip_client_id' => $vipClient->id]) }}">Nouvel envoi de colis</a>
            </div>
            
            <div style="padding: 10px 20px 22px;">
                <h3 style="font-size: 14px; margin-top: 10px; margin-bottom: 8px; color: var(--brand); font-family: 'Poppins', sans-serif;">Colis envoyés</h3>
                @if ($vipClient->parcels->isEmpty())
                    <p style="color: var(--muted); font-size: 13px; margin: 0 0 15px;">Aucun colis envoyé par ce client.</p>
                @else
                    <table class="data-table" style="font-size: 13px; margin-bottom: 15px;">
                        <thead><tr><th>Code</th><th>Destinataire</th><th>Trajet</th><th>Taille / Prix</th><th>Statut</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($vipClient->parcels as $parcel)
                                <tr>
                                    <td><strong>{{ $parcel->tracking_code }}</strong><small>{{ $parcel->created_at->format('d/m/Y H:i') }}</small></td>
                                    <td><strong>{{ $parcel->receiver_name }}</strong><small>{{ $parcel->receiver_phone }}</small></td>
                                    <td>{{ $parcel->originAgency->name }} → {{ $parcel->destinationAgency->name }}</td>
                                    <td>{{ ['document' => 'Document', 'small' => 'Petit', 'medium' => 'Moyen', 'large' => 'Grand', 'special' => 'Spécial'][$parcel->size_category] ?? $parcel->size_category }}<small>{{ number_format($parcel->shipping_price, 0, ',', ' ') }} FCFA</small></td>
                                    <td>
                                        <span class="status {{ $parcel->status === 'delivered' ? 'active' : 'pending_payment' }}" style="font-size: 9px; padding: 1px 4px;">
                                            {{ $parcel->status === 'delivered' ? 'Livré' : 'Enregistré' }}
                                        </span>
                                    </td>
                                    <td class="actions"><a href="{{ route('parcels.show', $parcel) }}">Ouvrir</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <h3 style="font-size: 14px; margin-top: 15px; margin-bottom: 8px; color: var(--brand); font-family: 'Poppins', sans-serif;">Colis reçus / destinés</h3>
                @if ($receivedParcels->isEmpty())
                    <p style="color: var(--muted); font-size: 13px; margin: 0;">Aucun colis reçu pour ce client.</p>
                @else
                    <table class="data-table" style="font-size: 13px;">
                        <thead><tr><th>Code</th><th>Expéditeur</th><th>Trajet</th><th>Taille</th><th>Statut</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($receivedParcels as $parcel)
                                <tr>
                                    <td><strong>{{ $parcel->tracking_code }}</strong><small>{{ $parcel->created_at->format('d/m/Y H:i') }}</small></td>
                                    <td><strong>{{ $parcel->sender_name }}</strong><small>{{ $parcel->sender_phone }}</small></td>
                                    <td>{{ $parcel->originAgency->name }} → {{ $parcel->destinationAgency->name }}</td>
                                    <td>{{ ['document' => 'Document', 'small' => 'Petit', 'medium' => 'Moyen', 'large' => 'Grand', 'special' => 'Spécial'][$parcel->size_category] ?? $parcel->size_category }}</td>
                                    <td>
                                        <span class="status {{ $parcel->status === 'delivered' ? 'active' : 'pending_payment' }}" style="font-size: 9px; padding: 1px 4px;">
                                            {{ $parcel->status === 'delivered' ? 'Livré' : 'Enregistré' }}
                                        </span>
                                    </td>
                                    <td class="actions"><a href="{{ route('parcels.show', $parcel) }}">Ouvrir</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>

        <div class="archive-row">
            @if ($vipClient->status !== 'archived')
            <form method="POST" action="{{ route('vip-clients.archive', $vipClient) }}" onsubmit="return confirm('Archiver ce client ? Son historique restera conserve.');">@csrf @method('PATCH')<button class="danger-action" type="submit">Archiver le client</button></form>
            @endif
            @if (auth()->user()->role?->slug === 'admin')
            <form method="POST" action="{{ route('vip-clients.destroy', $vipClient) }}" onsubmit="return confirm('Supprimer definitivement ce client sans historique de voyages ?');">@csrf @method('DELETE')<button class="danger-action delete-action" type="submit">Supprimer</button></form>
            @endif
        </div>
    </main>
</div>
@endsection
