@extends('layouts.app', ['title' => 'Paiements - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content">
        <div class="page-head">
            <div>
                <p class="section-tag">Encaissements</p>
                <h1>Gestion des paiements</h1>
                <p>Confirmez les paiements en attente pour activer les forfaits.</p>
            </div>
            @if ($pendingCount > 0)
                <span class="pending-badge">{{ $pendingCount }} en attente</span>
            @endif
        </div>

        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif

        <form class="filters payment-filters" method="GET" action="{{ route('payments.index') }}">
            <label class="search-field wide-filter">
                <span>Rechercher</span>
                <input type="search" name="search" value="{{ $search }}" placeholder="Reference, client, carte...">
            </label>
            <label class="select-field">
                <span>Statut</span>
                <select name="status" onchange="this.form.submit()">
                    <option value="pending" @selected($status === 'pending')>En attente</option>
                    <option value="paid" @selected($status === 'paid')>Payes</option>
                    <option value="all" @selected($status === 'all')>Tous</option>
                </select>
            </label>
            <button type="submit" class="filter-button">Filtrer</button>
        </form>

        <section class="data-panel">
            <table class="data-table">
                <thead><tr><th>Reference</th><th>Client</th><th>Carte / Forfait</th><th>Montant</th><th>Mode</th><th>Statut</th><th></th></tr></thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td><strong>{{ $payment->transaction_reference ?: 'Non renseignee' }}</strong><small>{{ $payment->created_at->format('d/m/Y H:i') }}</small></td>
                            <td>{{ $payment->client->first_name }} {{ $payment->client->last_name }}<small>{{ $payment->client->phone }}</small></td>
                            <td>
                                <strong>{{ $payment->subscription?->card?->card_number ?? '-' }}</strong>
                                <small>{{ $payment->subscription?->package?->name ?? 'Forfait' }}</small>
                            </td>
                            <td>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                            <td>{{ ['cash' => 'Especes', 'mobile_money' => 'Mobile Money', 'bank_transfer' => 'Virement'][$payment->payment_method] ?? $payment->payment_method }}</td>
                            <td><span class="status {{ $payment->payment_status === 'paid' ? 'active' : 'pending_payment' }}">{{ $payment->payment_status === 'paid' ? 'Paye' : 'En attente' }}</span></td>
                            <td class="actions">
                                <a href="{{ route('payments.show', $payment) }}">Voir</a>
                                @if ($payment->payment_status === 'pending')
                                    <a href="{{ route('payments.show', $payment) }}#confirmer">Confirmer</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="7">Aucun paiement ne correspond a votre recherche.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <div class="pagination">{{ $payments->links() }}</div>
    </main>
</div>
@endsection
