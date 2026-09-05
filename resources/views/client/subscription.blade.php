@extends('layouts.app', ['title' => 'Souscrire à un forfait - Buca Voyages VIP'])

@section('body')
<div class="shell">
    <header class="bar">
        <div class="brand-line">
            <strong>Buca Voyages VIP</strong>
            <a href="{{ route('client.portal') }}">Mon espace</a>
            <span>Nouveau forfait</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Deconnexion</button></form>
    </header>

    <main class="office-content narrow">
        <div class="page-head">
            <div>
                <p class="section-tag">Abonnements</p>
                <h1>{{ $currentSubscription ? 'Renouveler mon forfait' : 'Souscrire à un forfait' }}</h1>
                <p>Achetez un nouveau forfait de voyages prépayés pour votre carte <strong>{{ $card->card_number }}</strong>.</p>
            </div>
        </div>

        <form class="editor" method="POST" action="{{ route('client.subscription.store') }}">
            @csrf
            
            <input type="hidden" name="payment_method" value="mobile_money">

            <div class="form-grid">
                <label class="wide"><span>Formule / Package *</span>
                    <select name="package_id" id="package_id" required>
                        <option value="">Sélectionnez un forfait</option>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}" @selected(old('package_id') == $package->id) data-price="{{ $package->price }}">
                                {{ $package->name }} — {{ $package->trip_count }} voyages — {{ number_format($package->price, 0, ',', ' ') }} FCFA (valable {{ $package->validity_days }} jours)
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="wide"><span>Mode de paiement</span>
                    <div class="info-field" style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 6px; padding: 14px; display: flex; align-items: center; gap: 10px;">
                        <strong>Mobile Money (MTN MoMo / Orange Money)</strong>
                    </div>
                </label>

                <label class="wide"><span>Référence de paiement Mobile Money (Optionnel)</span>
                    <input type="text" name="transaction_reference" value="{{ old('transaction_reference') }}" placeholder="Ex: TXN102938475 (Laisser vide pour génération automatique)">
                </label>
            </div>

            @if ($currentSubscription && $currentSubscription->trips_remaining > 0)
                <label class="replace-check" style="margin-top: 20px;">
                    <input type="checkbox" name="confirm_replacement" value="1" @checked(old('confirm_replacement')) required>
                    <span>Je confirme vouloir remplacer mon forfait actuel qui contient encore {{ $currentSubscription->trips_remaining }} voyage(s).</span>
                </label>
            @endif

            @if ($errors->any())
                <div class="alert error" style="margin-top: 20px;">{{ $errors->first() }}</div>
            @endif

            <div class="form-buttons" style="margin-top: 25px;">
                <button class="primary action-button" type="submit" style="width: auto; padding: 0 28px;">Valider et acheter</button>
                <a class="secondary-link" href="{{ route('client.portal') }}">Annuler</a>
            </div>
        </form>
    </main>
</div>
@endsection
