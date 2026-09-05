@extends('layouts.app', ['title' => 'Nouvelle carte VIP - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content narrow">
        <div class="page-head">
            <div>
                <p class="section-tag">Carte de fidelite</p>
                <h1>Creer une carte VIP</h1>
                <p>Client : <strong>{{ $vipClient->first_name }} {{ $vipClient->last_name }}</strong></p>
            </div>
        </div>

        <form class="editor" method="POST" action="{{ route('vip-cards.store', $vipClient) }}">
            @csrf
            <div class="form-grid">
                <label><span>Type de carte *</span><select name="card_type" required><option value="vip" selected>VIP</option></select></label>
                <div class="info-field"><span>Numero de carte</span><strong>Genere automatiquement</strong></div>
            </div>
            @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
            <div class="form-buttons">
                <button class="primary action-button" type="submit">Creer la carte</button>
                <a class="secondary-link" href="{{ route('vip-clients.show', $vipClient) }}">Annuler</a>
            </div>
        </form>
    </main>
</div>
@endsection
