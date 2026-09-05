@extends('layouts.app', ['title' => 'Historique des voyages - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content">
        <div class="page-head">
            <div>
                <p class="section-tag">Operations</p>
                <h1>Historique des voyages consommes</h1>
                <p>Tous les decomptes effectues sur les cartes VIP.</p>
            </div>
            <a class="command primary-link" href="{{ route('trips.create') }}">Enregistrer un voyage</a>
        </div>

        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif

        <form class="filters" method="GET" action="{{ route('trips.index') }}">
            <label class="search-field wide-filter">
                <span>Rechercher</span>
                <input type="search" name="search" value="{{ $search }}" placeholder="Reference, client, carte...">
            </label>
            <button type="submit" class="filter-button">Filtrer</button>
        </form>

        <section class="data-panel">
            <table class="data-table">
                <thead><tr><th>Reference</th><th>Client</th><th>Voyageur</th><th>Carte</th><th>Trajet</th><th>Agence</th><th>Date</th><th>Solde debite</th></tr></thead>
                <tbody>
                    @forelse ($trips as $trip)
                        <tr>
                            <td><strong>{{ $trip->reference }}</strong><small>{{ $trip->consumed_at->format('d/m/Y H:i') }}</small></td>
                            <td>{{ $trip->client->first_name }} {{ $trip->client->last_name }}</td>
                            <td>{{ $trip->travelAffiliate?->fullName() ?? 'Titulaire' }}</td>
                            <td><a href="{{ route('vip-cards.show', $trip->card) }}">{{ $trip->card->card_number }}</a></td>
                            <td>{{ $trip->travelRoute->departure_city }} → {{ $trip->travelRoute->arrival_city }}</td>
                            <td>{{ $trip->departureAgency?->name ?? '-' }}</td>
                            <td>{{ $trip->travel_date->format('d/m/Y') }}</td>
                            <td>{{ $trip->trips_debited }} voyage(s)</td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="8">Aucun voyage consomme pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <div class="pagination">{{ $trips->links() }}</div>
    </main>
</div>
@endsection
