@extends('layouts.app', ['title' => 'Recu de paiement - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content narrow">
        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif

        <div class="page-head">
            <div>
                <p class="section-tag">Recu de paiement</p>
                <h1>{{ $payment->transaction_reference ?: 'Paiement #' . $payment->id }}</h1>
                <span class="status {{ $payment->payment_status === 'paid' ? 'active' : 'pending_payment' }}">{{ $payment->payment_status === 'paid' ? 'Paye' : 'En attente' }}</span>
            </div>
        </div>

        <section class="receipt-panel">
            <div class="receipt-header">
                <strong>Buca Voyages VIP</strong>
                <span>Reference : {{ $payment->transaction_reference ?: 'En attente de confirmation' }}</span>
            </div>
            <dl class="receipt-grid">
                <dt>Client</dt><dd>{{ $payment->client->first_name }} {{ $payment->client->last_name }}</dd>
                <dt>Telephone</dt><dd>{{ $payment->client->phone }}</dd>
                <dt>Carte VIP</dt><dd>@if ($payment->subscription?->card)<a href="{{ route('vip-cards.show', $payment->subscription->card) }}">{{ $payment->subscription->card->card_number }}</a>@else - @endif</dd>
                <dt>Forfait</dt><dd>{{ $payment->subscription?->package?->name ?? '-' }}</dd>
                <dt>Montant</dt><dd><strong class="amount-value">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</strong></dd>
                <dt>Mode de paiement</dt><dd>{{ ['cash' => 'Especes', 'mobile_money' => 'Mobile Money', 'bank_transfer' => 'Virement'][$payment->payment_method] ?? $payment->payment_method }}</dd>
                <dt>Date creation</dt><dd>{{ $payment->created_at->format('d/m/Y H:i') }}</dd>
                <dt>Date encaissement</dt><dd>{{ $payment->paid_at?->format('d/m/Y H:i') ?: 'Non confirme' }}</dd>
                <dt>Recu par</dt><dd>{{ $payment->receiver?->name ?? '-' }}</dd>
                <dt>Statut forfait</dt><dd>{{ ['active' => 'Actif', 'pending_payment' => 'Paiement en attente', 'renewed' => 'Renouvele', 'expired' => 'Expire', 'completed' => 'Epuise'][$payment->subscription?->status] ?? '-' }}</dd>
            </dl>
        </section>

        @if ($payment->payment_status === 'pending')
            <section class="editor confirm-panel" id="confirmer">
                <h2>Confirmer le paiement</h2>
                <p class="confirm-lead">Une fois confirme, le forfait associe sera active et les voyages pourront etre consommes.</p>
                <form method="POST" action="{{ route('payments.confirm', $payment) }}" onsubmit="return confirm('Confirmer ce paiement et activer le forfait ?');">
                    @csrf @method('PATCH')
                    <div class="form-grid">
                        <label class="wide"><span>Reference de paiement</span><input type="text" name="transaction_reference" value="{{ old('transaction_reference', $payment->transaction_reference) }}" placeholder="Laisser vide pour generation automatique"></label>
                    </div>
                    <div class="form-buttons">
                        <button class="primary action-button" type="submit">Confirmer le paiement</button>
                        <a class="secondary-link" href="{{ route('payments.index', ['status' => 'pending']) }}">Retour a la liste</a>
                    </div>
                </form>
            </section>
        @else
            <div class="form-buttons">
                <a class="secondary-link" href="{{ route('payments.index', ['status' => 'paid']) }}">Retour aux paiements payes</a>
            </div>
        @endif
    </main>
</div>
@endsection
