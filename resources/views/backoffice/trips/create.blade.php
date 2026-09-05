@extends('layouts.app', ['title' => 'Enregistrer un voyage - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content narrow">
        <div class="page-head">
            <div>
                <p class="section-tag">Consommation</p>
                <h1>Enregistrer un voyage</h1>
                <p>Debitez la carte du titulaire. Indiquez qui voyage reellement si ce n'est pas le titulaire.</p>
            </div>
        </div>

        @if ($cards->isEmpty())
            <div class="alert error">Aucune carte active avec solde disponible. Attribuez d'abord un forfait paye a un client.</div>
            <a class="secondary-link" href="{{ route('vip-clients.index') }}">Retour aux clients</a>
        @else
            <form class="editor" method="POST" action="{{ route('trips.store') }}" id="trip-form">
                @csrf
                <div class="form-grid">
                    <label class="wide"><span>Carte VIP *</span>
                        <select name="vip_card_id" id="vip_card_id" required>
                            <option value="">Choisir une carte</option>
                            @foreach ($cards as $card)
                                @php($subscription = $card->subscriptions->first())
                                <option value="{{ $card->id }}" data-client-id="{{ $card->vip_client_id }}" @selected(old('vip_card_id', $vipCard?->id) == $card->id)>
                                    {{ $card->card_number }} — {{ $card->client->first_name }} {{ $card->client->last_name }} ({{ $subscription?->trips_remaining ?? 0 }} voyage(s) restant(s))
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="wide"><span>Qui voyage ? *</span>
                        <select name="travel_affiliate_id" id="travel_affiliate_id">
                            <option value="">Titulaire de la carte</option>
                            @foreach ($affiliates as $affiliate)
                                <option value="{{ $affiliate->id }}" data-client-id="{{ $affiliate->vip_client_id }}" data-card-id="{{ $affiliate->vip_card_id ?? '' }}" @selected(old('travel_affiliate_id') == $affiliate->id) hidden>
                                    {{ $affiliate->first_name }} {{ $affiliate->last_name }}@if ($affiliate->relationship) ({{ $affiliate->relationship }})@endif
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="wide"><span>Trajet *</span>
                        <select name="travel_route_id" required>
                            <option value="">Choisir un trajet</option>
                            @foreach ($routes as $route)
                                <option value="{{ $route->id }}" @selected(old('travel_route_id') == $route->id)>{{ $route->departure_city }} → {{ $route->arrival_city }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="wide"><span>Agence de depart *</span>
                        <select name="departure_agency_id" required>
                            <option value="">Choisir une agence</option>
                            @foreach ($agencies as $agency)
                                <option value="{{ $agency->id }}" @selected(old('departure_agency_id') == $agency->id)>{{ $agency->name }} — {{ $agency->city }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label><span>Date du voyage</span><input type="date" name="travel_date" value="{{ old('travel_date', now()->format('Y-m-d')) }}"></label>
                    <label class="wide"><span>Notes</span><input type="text" name="notes" value="{{ old('notes') }}" placeholder="Optionnel"></label>
                </div>
                @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
                <div class="form-buttons">
                    <button class="primary action-button" type="submit">Valider le voyage</button>
                    <a class="secondary-link" href="{{ $vipCard ? route('vip-cards.show', $vipCard) : route('trips.index') }}">Annuler</a>
                </div>
            </form>
        @endif
    </main>
</div>
<script>
(function () {
    const cardSelect = document.getElementById('vip_card_id');
    const affiliateSelect = document.getElementById('travel_affiliate_id');
    if (!cardSelect || !affiliateSelect) return;

    function refreshAffiliates() {
        const selected = cardSelect.options[cardSelect.selectedIndex];
        const clientId = selected ? selected.dataset.clientId : '';
        const cardId = selected ? selected.value : '';

        Array.from(affiliateSelect.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            const matchesClient = option.dataset.clientId === clientId;
            const cardScope = option.dataset.cardId;
            const matchesCard = !cardScope || cardScope === cardId;
            option.hidden = !(matchesClient && matchesCard);
        });

        if (affiliateSelect.selectedOptions[0]?.hidden) {
            affiliateSelect.value = '';
        }
    }

    cardSelect.addEventListener('change', refreshAffiliates);
    refreshAffiliates();
})();
</script>
@endsection
