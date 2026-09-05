@extends('layouts.app', ['title' => 'Forfaits VIP - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')
    <main class="office-content">
        <div class="page-head">
            <div><p class="section-tag">Administration</p><h1>Gestion des forfaits</h1><p>Creez, modifiez et activez les formules de voyages prepayes.</p></div>
            <a class="command primary-link" href="{{ route('packages.create') }}">Nouvelle formule</a>
        </div>
        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
        <section class="data-panel">
            <table class="data-table">
                <thead><tr><th>Formule</th><th>Voyages</th><th>Validite</th><th>Prix</th><th>Classe</th><th>Statut</th><th></th></tr></thead>
                <tbody>
                    @forelse ($packages as $package)
                        <tr>
                            <td><strong>{{ $package->name }}</strong><small>{{ ['weekly' => 'Hebdomadaire', 'monthly' => 'Mensuel', 'annual' => 'Annuel', 'custom' => 'Personnalise'][$package->period_type] ?? $package->period_type }}@if ($package->travelRoute) — {{ $package->travelRoute->departure_city }} → {{ $package->travelRoute->arrival_city }}@endif</small></td>
                            <td>{{ $package->trip_count }}</td>
                            <td>{{ $package->validity_days }} jours</td>
                            <td>{{ number_format($package->price, 0, ',', ' ') }} FCFA</td>
                            <td>{{ ucfirst($package->class_type) }}</td>
                            <td><span class="status {{ $package->status === 'active' ? 'active' : 'archived' }}">{{ $package->status === 'active' ? 'Active' : 'Inactive' }}</span></td>
                            <td class="actions">
                                <a href="{{ route('packages.edit', $package) }}">Modifier</a>
                                <form method="POST" action="{{ route('packages.toggle-status', $package) }}" class="inline-form">@csrf @method('PATCH')<button type="submit">{{ $package->status === 'active' ? 'Desactiver' : 'Activer' }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="7">Aucune formule enregistree.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</div>
@endsection
