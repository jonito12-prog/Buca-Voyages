@extends('layouts.app', ['title' => 'Mon espace VIP - Buca Voyages VIP'])

@section('body')
<div class="shell">
    <header class="bar">
        <div class="brand-line"><strong>Buca Voyages VIP</strong><span>Mon espace</span></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Deconnexion</button></form>
    </header>

    <main class="office-content">
        @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif

        <div class="page-head">
            <div>
                <p class="section-tag">Espace client</p>
                <h1>Bonjour, {{ $user->name }}</h1>
                @if ($linked)
                    <p>Consultez votre carte VIP, votre solde et l'historique de vos voyages.</p>
                @else
                    <p>Votre compte n'est pas encore rattache a une fiche client VIP.</p>
                @endif
            </div>
        </div>

        @if (! $linked)
            <section class="client-placeholder">
                <p>Aucune carte VIP n'est associee a votre compte pour le moment.</p>
                <p>Rendez-vous en agence Buca Voyages ou contactez un agent pour activer votre fiche client avec la meme adresse email : <strong>{{ $user->email }}</strong></p>
            </section>
        @else
            @if ($card)
                <div class="vip-card-container">
                    <div class="vip-virtual-card {{ $card->status }}" id="vip-virtual-card">
                        <!-- Reflet brillant -->
                        <div class="card-shimmer"></div>
                        
                        <div class="card-header">
                            <div class="card-brand">BUCA VOYAGES</div>
                            <div class="card-type">VIP PASS</div>
                        </div>
                        
                        <!-- Puce électronique & icône sans contact -->
                        <div class="card-chip-row">
                            <div class="card-chip">
                                <div class="chip-line"></div><div class="chip-line"></div>
                                <div class="chip-line"></div><div class="chip-line"></div>
                            </div>
                            <div class="card-contactless">
                                <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2.5" fill="none">
                                    <path d="M5 8a9 9 0 0 1 14 0M7.5 10.5a6 6 0 0 1 9 0M10 13a3 3 0 0 1 4 0"></path>
                                    <circle cx="12" cy="18" r="1.5" fill="currentColor"></circle>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Numéro de la carte -->
                        <div class="card-number">{{ $card->card_number }}</div>
                        
                        <div class="card-footer">
                            <div class="card-holder">
                                <span class="card-label">Titulaire</span>
                                <span class="card-value">{{ $vipClient->first_name }} {{ $vipClient->last_name }}</span>
                            </div>
                            <div class="card-expiry">
                                <span class="card-label">Validité</span>
                                <span class="card-value">{{ $currentSubscription?->expires_at?->format('d/y') ?? 'MM/YY' }}</span>
                            </div>
                            <div class="card-status-badge">
                                <span class="status-indicator {{ $card->status }}"></span>
                                {{ ['active' => 'Active', 'suspended' => 'Suspendue', 'expired' => 'Expirée'][$card->status] ?? $card->status }}
                            </div>
                        </div>
                    </div>
                    
                    @if ($card->status === 'suspended' && $card->suspension_reason)
                        <div class="alert error" style="margin-top: 15px;">
                            <strong>Raison de la suspension :</strong> {{ $card->suspension_reason }}
                        </div>
                    @endif
                </div>

                <style>
                    /* Perspective du conteneur de la carte */
                    .vip-card-container {
                        perspective: 1000px;
                        margin-bottom: 25px;
                    }

                    /* Design de la carte virtuelle */
                    .vip-virtual-card {
                        width: 100%;
                        max-width: 400px;
                        height: 230px;
                        border-radius: 16px;
                        background: linear-gradient(135deg, #0A1A2F 0%, #1A365D 50%, #0A1A2F 100%);
                        color: #ffffff;
                        padding: 24px;
                        position: relative;
                        overflow: hidden;
                        box-shadow: 0 12px 24px rgba(10, 26, 47, 0.25);
                        border: 1px solid rgba(255, 255, 255, 0.15);
                        display: flex;
                        flex-direction: column;
                        justify-content: space-between;
                        transition: transform 0.6s ease, box-shadow 0.6s ease;
                        transform-style: preserve-3d;
                        cursor: pointer;
                        
                        /* Animation de révélation d'entrée, puis rotation/oscillation lente */
                        animation: cardReveal 1.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards,
                                   cardIdle 6s ease-in-out 1.2s infinite alternate;
                        opacity: 0;
                        transform: rotateX(15deg) rotateY(-10deg) translateY(20px);
                    }

                    .vip-virtual-card:hover {
                        animation-play-state: paused;
                        transition: transform 0.1s ease, box-shadow 0.2s ease;
                    }

                    @keyframes cardReveal {
                        0% {
                            opacity: 0;
                            transform: rotateX(30deg) rotateY(-20deg) translateY(40px) scale(0.9);
                        }
                        100% {
                            opacity: 1;
                            transform: rotateX(0deg) rotateY(0deg) translateY(0) scale(1);
                        }
                    }

                    @keyframes cardIdle {
                        0% {
                            transform: rotateX(2deg) rotateY(-6deg) translateY(0);
                            box-shadow: 0 12px 24px rgba(10, 26, 47, 0.25);
                        }
                        100% {
                            transform: rotateX(-2deg) rotateY(6deg) translateY(-5px);
                            box-shadow: 0 18px 30px rgba(10, 26, 47, 0.35);
                        }
                    }

                    /* Reflet métallique brillant balayant la carte */
                    .vip-virtual-card::after {
                        content: '';
                        position: absolute;
                        top: -50%;
                        left: -60%;
                        width: 200%;
                        height: 200%;
                        background: linear-gradient(
                            115deg,
                            rgba(255, 255, 255, 0) 0%,
                            rgba(255, 255, 255, 0) 40%,
                            rgba(255, 255, 255, 0.15) 48%,
                            rgba(255, 255, 255, 0.25) 50%,
                            rgba(255, 255, 255, 0.15) 52%,
                            rgba(255, 255, 255, 0) 60%,
                            rgba(255, 255, 255, 0) 100%
                        );
                        transform: rotate(-25deg);
                        transition: all 0.5s ease;
                        pointer-events: none;
                        animation: cardShine 8s infinite ease-in-out;
                    }

                    @keyframes cardShine {
                        0%, 100% {
                            transform: translate(-10%, -10%) rotate(-25deg);
                        }
                        50% {
                            transform: translate(10%, 10%) rotate(-25deg);
                        }
                    }

                    /* Surcharge de style pour les états suspendus et expirés */
                    .vip-virtual-card.suspended {
                        background: linear-gradient(135deg, #1E1B10 0%, #2D2715 50%, #1E1B10 100%);
                        border-color: rgba(254, 247, 224, 0.2);
                        box-shadow: 0 12px 24px rgba(176, 96, 0, 0.15);
                    }
                    .vip-virtual-card.expired {
                        background: linear-gradient(135deg, #1A1212 0%, #2A1818 50%, #1A1212 100%);
                        border-color: rgba(220, 38, 38, 0.2);
                    }

                    /* Éléments de la carte */
                    .vip-virtual-card .card-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }
                    .vip-virtual-card .card-brand {
                        font-family: 'Poppins', sans-serif;
                        font-weight: 800;
                        font-size: 16px;
                        letter-spacing: 2px;
                        color: #ffffff;
                    }
                    .vip-virtual-card .card-type {
                        font-size: 10px;
                        font-weight: 700;
                        letter-spacing: 3px;
                        background: linear-gradient(135deg, #D90429 0%, #FF6B8B 100%);
                        padding: 3px 8px;
                        border-radius: 4px;
                    }
                    .vip-virtual-card.suspended .card-type {
                        background: #B06000;
                    }
                    .vip-virtual-card.expired .card-type {
                        background: #5F6368;
                    }

                    /* Puce électronique */
                    .card-chip-row {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-top: 10px;
                    }
                    .card-chip {
                        width: 38px;
                        height: 28px;
                        background: linear-gradient(135deg, #ECC94B 0%, #D69E2E 100%);
                        border-radius: 4px;
                        position: relative;
                        padding: 3px;
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 1px;
                    }
                    .chip-line {
                        border: 1px solid rgba(0, 0, 0, 0.15);
                        border-radius: 2px;
                    }
                    .card-contactless {
                        color: rgba(255, 255, 255, 0.6);
                    }

                    /* Numéro de carte monospécifié */
                    .card-number {
                        font-family: 'Courier New', Courier, monospace;
                        font-size: 20px;
                        letter-spacing: 2.5px;
                        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
                        margin: 15px 0;
                        font-weight: 600;
                        color: #F8FAFC;
                    }

                    /* Pied de carte */
                    .card-footer {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                    }
                    .card-label {
                        display: block;
                        font-size: 8px;
                        text-transform: uppercase;
                        color: rgba(255, 255, 255, 0.5);
                        letter-spacing: 1px;
                        margin-bottom: 3px;
                    }
                    .card-value {
                        font-size: 13px;
                        font-weight: 600;
                        letter-spacing: 0.5px;
                    }
                    .card-status-badge {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        font-size: 11px;
                        font-weight: 700;
                        background: rgba(255, 255, 255, 0.1);
                        padding: 4px 10px;
                        border-radius: 20px;
                        border: 1px solid rgba(255, 255, 255, 0.1);
                    }
                    .status-indicator {
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        background-color: #D90429;
                    }
                    .status-indicator.active {
                        background-color: #4ade80;
                        box-shadow: 0 0 8px #4ade80;
                    }
                    .status-indicator.suspended {
                        background-color: #fbbf24;
                    }
                    .status-indicator.expired {
                        background-color: #94a3b8;
                    }
                </style>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const card = document.getElementById('vip-virtual-card');
                        if (card) {
                            card.addEventListener('mousemove', function(e) {
                                const rect = card.getBoundingClientRect();
                                const x = e.clientX - rect.left;
                                const y = e.clientY - rect.top;
                                
                                const centerX = rect.width / 2;
                                const centerY = rect.height / 2;
                                
                                const rotateX = ((centerY - y) / centerY) * 12;
                                const rotateY = ((x - centerX) / centerX) * 12;
                                
                                card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.03)`;
                                card.style.boxShadow = '0 20px 35px rgba(10, 26, 47, 0.35)';
                            });
                            
                            card.addEventListener('mouseleave', function() {
                                card.style.transform = '';
                                card.style.boxShadow = '';
                            });
                        }
                    });
                </script>

                <div class="metric-grid">
                    <section class="metric important"><span>Voyages restants</span><strong>{{ $currentSubscription?->trips_remaining ?? 0 }}</strong></section>
                    <section class="metric"><span>Voyages achetes</span><strong>{{ $currentSubscription?->trips_total ?? 0 }}</strong></section>
                    <section class="metric"><span>Fin de validite</span><strong class="date-value">{{ $currentSubscription?->expires_at?->format('d/m/Y') ?? '-' }}</strong></section>
                </div>

                @if ($currentSubscription?->status === 'pending_payment')
                    <div class="alert warn">Votre forfait est en attente de confirmation de paiement en agence.</div>
                @endif

                <div class="detail-grid">
                    <section class="detail-panel">
                        <h2>Forfait en cours</h2>
                        @if ($currentSubscription)
                            <dl>
                                <dt>Formule</dt><dd>{{ $currentSubscription->package->name }}</dd>
                                <dt>Montant</dt><dd>{{ number_format($currentSubscription->total_amount, 0, ',', ' ') }} FCFA</dd>
                                <dt>Validite</dt><dd>{{ $currentSubscription->starts_at->format('d/m/Y') }} - {{ $currentSubscription->expires_at->format('d/m/Y') }}</dd>
                                <dt>Statut</dt><dd>{{ ['active' => 'Actif', 'pending_payment' => 'Paiement en attente', 'renewed' => 'Renouvele', 'expired' => 'Expire', 'completed' => 'Epuise'][$currentSubscription->status] ?? $currentSubscription->status }}</dd>
                            </dl>
                            <div style="margin-top: 20px; padding: 0 0 10px;">
                                <a href="{{ route('client.subscription.form') }}" class="command primary-link compact" style="display: inline-flex;">Renouveler mon forfait</a>
                            </div>
                        @else
                            <p class="empty-block" style="margin-bottom: 15px;">Aucun forfait actif sur cette carte.</p>
                            <div style="padding: 0 20px 22px;">
                                <a href="{{ route('client.subscription.form') }}" class="command primary-link compact" style="display: inline-flex;">Souscrire à un forfait</a>
                            </div>
                        @endif
                    </section>
                    <section class="detail-panel">
                        <h2>Mes coordonnees & Securite</h2>
                        <dl style="margin-bottom: 15px;">
                            <dt>Nom</dt><dd>{{ $vipClient->first_name }} {{ $vipClient->last_name }}</dd>
                            <dt>Telephone</dt><dd>{{ $vipClient->phone }}</dd>
                            <dt>Email</dt><dd>{{ $vipClient->email ?: 'Non renseigne' }}</dd>
                        </dl>
                        
                        <div style="border-top: 1px dashed var(--line); padding-top: 15px;">
                            <a href="#password-form-container" onclick="document.getElementById('password-form-container').style.display='block'; this.style.display='none'; return false;" class="command secondary-link compact" style="display: {{ ($errors->has('current_password') || $errors->has('password')) ? 'none' : 'inline-flex' }};">Modifier mon mot de passe</a>
                            
                            <div id="password-form-container" style="display: {{ ($errors->has('current_password') || $errors->has('password')) ? 'block' : 'none' }};">
                                <form method="POST" action="{{ route('client.change-password') }}" class="editor" style="border: 0; padding: 0; box-shadow: none; background: transparent;">
                                    @csrf
                                    <label style="margin: 10px 0 5px;"><span>Mot de passe actuel *</span>
                                        <input type="password" name="current_password" required style="height: 38px; font-size: 13px;">
                                    </label>
                                    <label style="margin: 10px 0 5px;"><span>Nouveau mot de passe *</span>
                                        <input type="password" name="password" required style="height: 38px; font-size: 13px;">
                                    </label>
                                    <label style="margin: 10px 0 5px;"><span>Confirmer le mot de passe *</span>
                                        <input type="password" name="password_confirmation" required style="height: 38px; font-size: 13px;">
                                    </label>
                                    @if ($errors->has('current_password') || $errors->has('password'))
                                        <div class="alert error" style="margin-top: 10px; padding: 8px 12px; font-size: 13px;">
                                            {{ $errors->first('current_password') ?: $errors->first('password') }}
                                        </div>
                                    @endif
                                    <div style="margin-top: 15px; display: flex; gap: 10px;">
                                        <button type="submit" class="primary compact-btn" style="min-height: 34px; padding: 0 12px; font-size: 12px; width: auto; background: var(--brand); color: white;">Enregistrer</button>
                                        <button type="button" onclick="document.getElementById('password-form-container').style.display='none'; document.querySelector('a[href=\'#password-form-container\']').style.display='inline-flex';" class="secondary-link compact-btn" style="min-height: 34px; padding: 0 12px; font-size: 12px;">Annuler</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="data-panel section-panel">
                    <div class="panel-title"><h2>Historique des forfaits</h2><span>{{ $card->subscriptions->count() }} operation(s)</span></div>
                    @if ($card->subscriptions->isEmpty())
                        <p class="empty-block">Aucun forfait enregistre.</p>
                    @else
                        <table class="data-table">
                            <thead><tr><th>Forfait</th><th>Solde</th><th>Validite</th><th>Statut</th></tr></thead>
                            <tbody>
                                @foreach ($card->subscriptions as $subscription)
                                    <tr>
                                        <td><strong>{{ $subscription->package->name }}</strong></td>
                                        <td>{{ $subscription->trips_remaining }} / {{ $subscription->trips_total }}</td>
                                        <td>{{ $subscription->starts_at->format('d/m/Y') }} - {{ $subscription->expires_at->format('d/m/Y') }}</td>
                                        <td><span class="status {{ $subscription->status }}">{{ ['active' => 'Actif', 'pending_payment' => 'Paiement en attente', 'renewed' => 'Renouvele', 'expired' => 'Expire', 'completed' => 'Epuise'][$subscription->status] ?? $subscription->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>
            @else
                <section class="client-placeholder">
                    <p>Votre fiche client est active, mais aucune carte VIP ne vous a encore ete attribuee.</p>
                </section>
            @endif

            <section class="data-panel section-panel">
                <div class="panel-title"><h2>Personnes autorisees a voyager pour moi</h2><span>{{ $affiliates->count() }} personne(s)</span></div>
                <p class="empty-block intro-note">Si vous ne pouvez pas voyager, ajoutez une personne de confiance qui pourra utiliser votre carte VIP en agence.</p>
                @if ($affiliates->isNotEmpty())
                    <table class="data-table">
                        <thead><tr><th>Nom</th><th>Lien</th><th>Carte</th><th>Statut</th></tr></thead>
                        <tbody>
                            @foreach ($affiliates as $affiliate)
                                <tr>
                                    <td><strong>{{ $affiliate->first_name }} {{ $affiliate->last_name }}</strong></td>
                                    <td>{{ $affiliate->relationship ?: '-' }}</td>
                                    <td>{{ $affiliate->card?->card_number ?? 'Toutes cartes' }}</td>
                                    <td><span class="status {{ $affiliate->status === 'active' ? 'active' : 'archived' }}">{{ $affiliate->status === 'active' ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
                <form class="editor affiliate-form" method="POST" action="{{ route('client.affiliates.store') }}">@csrf
                    <div class="form-grid">
                        <label><span>Prenom *</span><input type="text" name="first_name" value="{{ old('first_name') }}" required></label>
                        <label><span>Nom *</span><input type="text" name="last_name" value="{{ old('last_name') }}" required></label>
                        <label><span>Telephone</span><input type="text" name="phone" value="{{ old('phone') }}"></label>
                        <label><span>Lien</span>
                            <select name="relationship">
                                <option value="">Choisir</option>
                                @foreach (['Conjoint', 'Enfant', 'Parent', 'Employe', 'Assistant', 'Autre'] as $relationship)
                                    <option value="{{ $relationship }}" @selected(old('relationship') === $relationship)>{{ $relationship }}</option>
                                @endforeach
                            </select>
                        </label>
                        @if ($card)
                            <label class="wide"><span>Carte concernee</span>
                                <select name="vip_card_id">
                                    <option value="">Toutes mes cartes</option>
                                    <option value="{{ $card->id }}" @selected(old('vip_card_id') == $card->id)>{{ $card->card_number }}</option>
                                </select>
                            </label>
                        @endif
                    </div>
                    <div class="form-buttons"><button class="primary action-button compact-btn" type="submit">Ajouter une personne</button></div>
                </form>
            </section>

            <section class="data-panel section-panel">
                <div class="panel-title"><h2>Historique de mes voyages</h2><span>{{ $consumptions->count() }} voyage(s)</span></div>
                @if ($consumptions->isEmpty())
                    <p class="empty-block">Aucun voyage consomme pour le moment.</p>
                @else
                    <table class="data-table">
                        <thead><tr><th>Reference</th><th>Voyageur</th><th>Trajet</th><th>Agence</th><th>Date</th><th>Debite</th></tr></thead>
                        <tbody>
                            @foreach ($consumptions as $consumption)
                                <tr>
                                    <td><strong>{{ $consumption->reference }}</strong><small>{{ $consumption->consumed_at->format('d/m/Y H:i') }}</small></td>
                                    <td>{{ $consumption->travelAffiliate?->fullName() ?? 'Titulaire' }}</td>
                                    <td>{{ $consumption->travelRoute->departure_city }} → {{ $consumption->travelRoute->arrival_city }}</td>
                                    <td>{{ $consumption->departureAgency?->name ?? '-' }}</td>
                                    <td>{{ $consumption->travel_date->format('d/m/Y') }}</td>
                                    <td>{{ $consumption->trips_debited }} voyage(s)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        @endif
    </main>
</div>

@include('client._chatbot')
@endsection
