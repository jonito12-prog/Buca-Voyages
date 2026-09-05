@extends('layouts.app', ['title' => 'Gestion des Colis - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content">
        <div class="page-head">
            <div>
                <p class="section-tag">Messagerie & Logistique</p>
                <h1>Gestion des envois de colis</h1>
                <p>Enregistrez et suivez les colis expédiés entre les différentes agences.</p>
            </div>
            <a class="command primary-link" href="{{ route('parcels.create') }}">Nouvel envoi de colis</a>
        </div>

        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif

        <form class="filters" method="GET" action="{{ route('parcels.index') }}" style="grid-template-columns: 2fr 1.5fr 1.5fr 1.2fr 100px;">
            <label class="search-field">
                <span>Rechercher</span>
                <input type="search" name="search" value="{{ $search }}" placeholder="Code, expéditeur, destinataire...">
            </label>

            <label>
                <span>Statut</span>
                <select name="status">
                    <option value="all">Tous les statuts</option>
                    @foreach(['registered' => 'Enregistré', 'delivered' => 'Livré'] as $k => $v)
                        <option value="{{ $k }}" @selected($status === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Origine</span>
                <select name="origin">
                    <option value="">Toutes agences</option>
                    @foreach($agencies as $agency)
                        <option value="{{ $agency->id }}" @selected($origin == $agency->id)>{{ $agency->name }} — {{ $agency->city }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Destination</span>
                <select name="destination">
                    <option value="">Toutes agences</option>
                    @foreach($agencies as $agency)
                        <option value="{{ $agency->id }}" @selected($destination == $agency->id)>{{ $agency->name }} — {{ $agency->city }}</option>
                    @endforeach
                </select>
            </label>

            <button type="submit" class="filter-button">Filtrer</button>
        </form>

        <section class="data-panel">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code / Date</th>
                        <th>Expéditeur</th>
                        <th>Destinataire</th>
                        <th>Trajet</th>
                        <th>Taille / Prix</th>
                        <th>Paiement</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($parcels as $parcel)
                        <tr>
                            <td>
                                <strong>{{ $parcel->tracking_code }}</strong>
                                <small>{{ $parcel->registered_at ? $parcel->registered_at->format('d/m/Y H:i') : $parcel->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <strong>{{ $parcel->sender_name }}</strong>
                                <small>{{ $parcel->sender_phone }}</small>
                                @if($parcel->sender)
                                    <span class="status active" style="font-size: 9px; padding: 1px 4px; margin-top: 2px;">VIP</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $parcel->receiver_name }}</strong>
                                <small>{{ $parcel->receiver_phone }}</small>
                            </td>
                            <td>
                                <strong>{{ $parcel->originAgency->name }}</strong>
                                <small>→ {{ $parcel->destinationAgency->name }}</small>
                            </td>
                            <td>
                                <strong>{{ number_format($parcel->shipping_price, 0, ',', ' ') }} FCFA</strong>
                                <small>{{ ['document' => 'Document', 'small' => 'Petit', 'medium' => 'Moyen', 'large' => 'Grand', 'special' => 'Spécial'][$parcel->size_category] ?? $parcel->size_category }}</small>
                            </td>
                            <td>
                                <strong>{{ ['cash' => 'Espèces', 'momo' => 'MTN MoMo', 'orange_money' => 'Orange Money', 'vip_card_debit' => 'Débit Carte VIP'][$parcel->payment_method] ?? $parcel->payment_method }}</strong>
                                <span class="status {{ $parcel->payment_status === 'paid' ? 'active' : 'suspended' }}" style="font-size: 10px; padding: 1px 6px;">
                                    {{ $parcel->payment_status === 'paid' ? 'Payé' : 'En attente' }}
                                </span>
                            </td>
                            <td>
                                <span class="status {{ $parcel->status === 'delivered' ? 'active' : 'pending_payment' }}">
                                    {{ $parcel->status === 'delivered' ? 'Livré' : 'Enregistré' }}
                                </span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('parcels.show', $parcel) }}">Ouvrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="8">Aucun envoi de colis enregistré pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <div class="pagination">{{ $parcels->links() }}</div>
    </main>
</div>
@endsection
