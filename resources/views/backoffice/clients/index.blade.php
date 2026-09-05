@extends('layouts.app', ['title' => 'Clients VIP - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content">
        <div class="page-head">
            <div>
                <p class="section-tag">Fidelisation</p>
                <h1>Clients VIP</h1>
                <p>Retrouvez les clients, suivez leur statut et preparez l'attribution de leurs cartes.</p>
            </div>
            <a class="command primary-link" href="{{ route('vip-clients.create') }}">Nouveau client</a>
        </div>

        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif

        <form class="filters" method="GET" action="{{ route('vip-clients.index') }}">
            <label class="search-field">
                <span>Rechercher</span>
                <input type="search" name="search" value="{{ $search }}" placeholder="Nom, telephone, email ou piece">
            </label>
            <label class="select-field">
                <span>Statut</span>
                <select name="status" onchange="this.form.submit()">
                    <option value="all" @selected($status === 'all')>Tous</option>
                    <option value="active" @selected($status === 'active')>Actifs</option>
                    <option value="suspended" @selected($status === 'suspended')>Suspendus</option>
                    <option value="archived" @selected($status === 'archived')>Archives</option>
                    <option value="pending" @selected($status === 'pending')>En attente (En ligne)</option>
                </select>
            </label>
            <button type="submit" class="filter-button">Filtrer</button>
        </form>

        <section class="data-panel">
            <table class="data-table">
                <thead><tr><th>Client</th><th>Telephone</th><th>Statut</th><th>Cartes</th><th></th></tr></thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td><strong>{{ $client->first_name }} {{ $client->last_name }}</strong><small>{{ $client->email ?: 'Aucun email' }}</small></td>
                            <td>{{ $client->phone }}</td>
                            <td><span class="status {{ $client->status }}">{{ ['active' => 'Actif', 'suspended' => 'Suspendu', 'archived' => 'Archive', 'pending' => 'En attente (En ligne)'][$client->status] ?? $client->status }}</span></td>
                            <td>{{ $client->cards_count }}</td>
                            <td class="actions">
                                @if ($client->status === 'pending')
                                    <a href="{{ route('vip-clients.create', ['email' => $client->email, 'first_name' => $client->first_name, 'last_name' => $client->last_name, 'phone' => $client->phone]) }}">Créer la fiche</a>
                                @else
                                    <a href="{{ route('vip-clients.show', $client) }}">Voir</a>
                                    <a href="{{ route('vip-clients.edit', $client) }}">Modifier</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="5">Aucun client ne correspond a votre recherche.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <div class="pagination">{{ $clients->links() }}</div>
    </main>
</div>
@endsection
