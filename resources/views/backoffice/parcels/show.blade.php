@extends('layouts.app', ['title' => 'Détails du colis - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content">
        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif

        <div class="page-head">
            <div>
                <p class="section-tag">Suivi de colis</p>
                <h1 style="display: inline-flex; align-items: center; gap: 10px;">
                    Colis {{ $parcel->tracking_code }}
                    <span class="status {{ $parcel->status === 'delivered' ? 'active' : 'pending_payment' }}" style="font-size: 14px;">
                        {{ $parcel->status === 'delivered' ? 'Livré' : 'Enregistré' }}
                    </span>
                </h1>
                <p>Créé par {{ $parcel->creator?->name ?: 'Système' }} le {{ $parcel->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <a class="secondary-link" href="{{ route('parcels.index') }}">Retour à la liste</a>
        </div>



        <div class="detail-grid">
            <!-- Informations Expédition -->
            <section class="detail-panel">
                <h2>Expéditeur & Destinataire</h2>
                <dl>
                    <dt>Expéditeur</dt>
                    <dd>
                        {{ $parcel->sender_name }}
                        @if($parcel->sender)
                            <a href="{{ route('vip-clients.show', $parcel->sender) }}" class="status active" style="font-size: 10px; margin-left: 5px; padding: 1px 6px;">VIP</a>
                        @endif
                    </dd>
                    
                    <dt>Téléphone Exp.</dt>
                    <dd>{{ $parcel->sender_phone }}</dd>
                    
                    @if($parcel->sender_email)
                        <dt>E-mail Exp.</dt>
                        <dd>{{ $parcel->sender_email }}</dd>
                    @endif
                    
                    <dt>Destinataire</dt>
                    <dd>{{ $parcel->receiver_name }}</dd>
                    
                    <dt>Téléphone Dest.</dt>
                    <dd>{{ $parcel->receiver_phone }}</dd>

                    @if($parcel->receiver_identity_number)
                        <dt>CNI Dest. (Retrait)</dt>
                        <dd style="color: var(--brand); font-weight: bold;">{{ $parcel->receiver_identity_number }}</dd>
                    @endif
                </dl>
            </section>

            <!-- Détails Colis et Paiement -->
            <section class="detail-panel">
                <h2>Détails Logistiques & Paiement</h2>
                <dl>
                    <dt>Origine</dt>
                    <dd>{{ $parcel->originAgency->name }} ({{ $parcel->originAgency->city }})</dd>
                    
                    <dt>Destination</dt>
                    <dd>{{ $parcel->destinationAgency->name }} ({{ $parcel->destinationAgency->city }})</dd>
                    
                    <dt>Volume</dt>
                    <dd>{{ ['document' => 'Document / Enveloppe', 'small' => 'Petit colis', 'medium' => 'Colis Moyen', 'large' => 'Grand carton / Sac', 'special' => 'Électroménager / Spécial'][$parcel->size_category] ?? $parcel->size_category }}</dd>
                    
                    <dt>Valeur déclarée</dt>
                    <dd>{{ number_format($parcel->declared_value, 0, ',', ' ') }} FCFA</dd>
                    
                    <dt>Prix de transport</dt>
                    <dd class="amount-value" style="font-size: 20px;">{{ number_format($parcel->shipping_price, 0, ',', ' ') }} FCFA</dd>

                    <dt>Mode paiement</dt>
                    <dd>{{ ['cash' => 'Espèces', 'momo' => 'MTN MoMo', 'orange_money' => 'Orange Money'][$parcel->payment_method] ?? $parcel->payment_method }}</dd>

                    <dt>Statut paiement</dt>
                    <dd>
                        <span class="status {{ $parcel->payment_status === 'paid' ? 'active' : 'suspended' }}">
                            {{ $parcel->payment_status === 'paid' ? 'Payé' : 'En attente' }}
                        </span>
                    </dd>
                </dl>
            </section>
        </div>

        @if($parcel->notes)
            <div style="background: #f8fafc; border: 1px solid var(--line); border-radius: 8px; padding: 15px 20px; margin-bottom: 20px;">
                <h3 style="margin: 0 0 5px; font-size: 13px; text-transform: uppercase; color: var(--muted);">Description du contenu / Notes</h3>
                <p style="margin: 0; font-size: 14px; font-weight: 500;">{{ $parcel->notes }}</p>
            </div>
        @endif

        <!-- Actions de Retrait / Livraison -->
        <section class="data-panel" style="padding: 22px;">
            <h2 style="margin: 0 0 15px; font-size: 18px; font-family: 'Poppins', sans-serif;">Retrait du Colis</h2>
            
            <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
                @if ($parcel->status === 'registered')
                    <form method="POST" action="{{ route('parcels.deliver', $parcel) }}" style="width: 100%; max-width: 500px; padding: 15px; background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px;">
                        @csrf @method('PATCH')
                        <h3 style="margin: 0 0 10px; font-size: 14px; color: #065F46;">Confirmer le retrait par le destinataire</h3>
                        <label style="margin-top: 0;">
                            <span style="color: #065F46;">Numéro de pièce d'identité (CNI, Passeport, Permis...) *</span>
                            <input type="text" name="receiver_identity_number" required placeholder="Ex: CNI N° 102938475" style="border-color: #a7f3d0; margin-top: 5px;">
                        </label>
                        <button class="primary action-button" type="submit" style="background: #059669; margin-top: 12px; width: auto;">Marquer le colis comme livré</button>
                    </form>
                @else
                    <p style="color: #137333; font-weight: 600; font-size: 15px; margin: 0; display: flex; align-items: center; gap: 8px;">
                        ✓ Ce colis a été retiré et livré. (Pièce d'identité enregistrée : <strong>{{ $parcel->receiver_identity_number }}</strong>)
                    </p>
                @endif
            </div>
        </section>
    </main>
</div>
@endsection
