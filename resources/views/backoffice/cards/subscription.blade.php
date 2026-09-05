@extends('layouts.app', ['title' => 'Attribuer un forfait - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content narrow">
        <div class="page-head">
            <div>
                <p class="section-tag">Voyages prepayes</p>
                <h1>{{ $currentSubscription ? 'Renouveler le forfait' : 'Attribuer un forfait' }}</h1>
                <p>Carte {{ $vipCard->card_number }}</p>
            </div>
        </div>

        <form class="editor" method="POST" action="{{ route('vip-cards.subscribe', $vipCard) }}">
            @csrf
            <div class="form-grid">
                <label class="wide"><span>Formule *</span>
                    <select name="package_id" required>
                        <option value="">Choisir une formule</option>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}" @selected(old('package_id') == $package->id)>{{ $package->name }} - {{ $package->trip_count }} voyages - {{ number_format($package->price, 0, ',', ' ') }} FCFA / {{ $package->validity_days }} jours</option>
                        @endforeach
                    </select>
                </label>
                <label><span>Mode de paiement *</span>
                    <select name="payment_method" required>
                        <option value="cash" @selected(old('payment_method') === 'cash')>Especes</option>
                        <option value="mobile_money" @selected(old('payment_method') === 'mobile_money')>Mobile Money</option>
                        <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Virement</option>
                    </select>
                </label>
                <label><span>Statut du paiement *</span>
                    <select name="payment_status" required>
                        <option value="paid" @selected(old('payment_status', 'paid') === 'paid')>Paye</option>
                        <option value="pending" @selected(old('payment_status') === 'pending')>En attente</option>
                    </select>
                </label>
                <label class="wide"><span>Reference de paiement</span><input type="text" name="transaction_reference" value="{{ old('transaction_reference') }}" placeholder="Laisser vide pour generation automatique"></label>
            </div>
            @if ($currentSubscription && $currentSubscription->trips_remaining > 0)
                <label class="replace-check"><input type="checkbox" name="confirm_replacement" value="1" @checked(old('confirm_replacement'))><span>Je confirme le renouvellement. Le forfait actuel contient encore {{ $currentSubscription->trips_remaining }} voyage(s) restant(s).</span></label>
            @endif
            @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
            <div class="form-buttons">
                <button class="primary action-button" type="submit">{{ $currentSubscription ? 'Renouveler' : 'Attribuer le forfait' }}</button>
                <a class="secondary-link" href="{{ route('vip-cards.show', $vipCard) }}">Annuler</a>
            </div>
        </form>
    </main>
</div>
@endsection
