@extends('layouts.app', ['title' => 'Tableau de bord - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="modern-dash-wrap">
        @if (session('status'))
            <div class="alert ok" style="margin-bottom: 20px; border-radius: 12px;">{{ session('status') }}</div>
        @endif

        @if ($isStaff)
            <!-- Topbar Header -->
            <div class="dash-topbar">
                <div class="dash-heading">
                    <h1>Tableau de bord VIP</h1>
                    <p>Supervisez les souscriptions VIP, départs, expéditions et flux d'agence en temps réel.</p>
                </div>
                <div class="dash-actions">
                    <a class="dash-btn dash-btn-primary" href="{{ route('vip-clients.create') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Nouveau Client VIP
                    </a>
                    <a class="dash-btn dash-btn-secondary" href="{{ route('trips.create') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.9 2 11.2 2 11.5V16c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
                        Voyage
                    </a>
                    <a class="dash-btn dash-btn-secondary" href="{{ route('parcels.create') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
                        Colis
                    </a>
                </div>
            </div>

            <!-- Top 4 KPI Metrics -->
            <div class="dash-kpi-grid">
                <!-- KPI 1 : Hero Dark Crimson Card -->
                <a class="dash-kpi-card hero" href="{{ route('vip-clients.index') }}">
                    <div class="kpi-head">
                        <span class="kpi-label">Clients VIP Actifs</span>
                        <div class="kpi-circle-btn">↗</div>
                    </div>
                    <div class="kpi-main-val">{{ $kpis['vip_clients'] }}</div>
                    <div class="kpi-foot">
                        <span class="hero-pill">Club Privilège Buca</span>
                    </div>
                </a>

                <!-- KPI 2 : Active Cards -->
                <a class="dash-kpi-card standard" href="{{ route('vip-clients.index') }}">
                    <div class="kpi-head">
                        <span class="kpi-label">Cartes VIP en circulation</span>
                        <div class="kpi-circle-btn">↗</div>
                    </div>
                    <div class="kpi-main-val">{{ $kpis['active_cards'] }}</div>
                    <div class="kpi-foot">
                        <span class="trend-pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                            {{ $kpis['suspended_cards'] }} suspendue(s)
                        </span>
                    </div>
                </a>

                <!-- KPI 3 : Consumed Trips -->
                <a class="dash-kpi-card standard" href="{{ route('trips.index') }}">
                    <div class="kpi-head">
                        <span class="kpi-label">Voyages Consommés</span>
                        <div class="kpi-circle-btn">↗</div>
                    </div>
                    <div class="kpi-main-val">{{ $kpis['consumed_trips'] }}</div>
                    <div class="kpi-foot">
                        <span class="trend-pill subtle">
                            {{ $kpis['trips_today'] }} enregistrés aujourd'hui
                        </span>
                    </div>
                </a>

                <!-- KPI 4 : Parcels & Logistics -->
                <a class="dash-kpi-card standard" href="{{ route('parcels.index') }}">
                    <div class="kpi-head">
                        <span class="kpi-label">Colis & Messagerie</span>
                        <div class="kpi-circle-btn">↗</div>
                    </div>
                    <div class="kpi-main-val">{{ $kpis['registered_parcels'] }}</div>
                    <div class="kpi-foot">
                        <span class="trend-pill">
                            Total : {{ $kpis['total_parcels'] }} expéditions
                        </span>
                    </div>
                </a>
            </div>

            <!-- Middle Row : Analytics, Reminders/Next Departure, Recent Trips -->
            <div class="dash-row-grid">
                <!-- Card 1: Weekly Activity Chart -->
                <div class="dash-card">
                    <div class="dash-card-head">
                        <h2>Activité Hebdomadaire</h2>
                        <span class="dash-card-pill brand">7 jours</span>
                    </div>
                    <div class="analytics-chart">
                        @foreach ($weeklyDays as $day)
                            <div class="chart-col {{ $day['isToday'] ? 'is-today' : '' }}">
                                <div class="chart-bar-wrap" title="{{ $day['label'] }} ({{ $day['date'] }}) : {{ $day['total'] }} opération(s) ({{ $day['trips'] }} voyages, {{ $day['parcels'] }} colis)">
                                    <div class="chart-bar {{ $day['isToday'] ? 'active-day' : ($day['total'] > 0 ? 'past-day' : 'striped') }}" style="height: {{ $day['bar_height_pct'] }}%;"></div>
                                </div>
                                <span class="chart-label">{{ $day['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Card 2: Featured Departure / Reminders -->
                <div class="dash-card">
                    <div class="dash-card-head">
                        <h2>Prochains Départs VIP</h2>
                        <span class="dash-card-pill brand">Direct</span>
                    </div>
                    <div>
                        <div class="departure-box">
                            <div class="departure-route">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D90429" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                {{ $featuredRoute ? $featuredRoute->departure_city . ' ➔ ' . $featuredRoute->arrival_city : 'Yaoundé ➔ Douala VIP' }}
                            </div>
                            <div class="departure-time">
                                Départ Quai VIP • {{ $featuredRoute ? $featuredRoute->estimated_duration : '4h30 de trajet' }}
                            </div>
                        </div>
                        <a class="departure-btn" href="{{ route('trips.create') }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                            Valider un Embarquement
                        </a>
                    </div>
                </div>

                <!-- Card 3: Recent Trips Task List -->
                <div class="dash-card">
                    <div class="dash-card-head">
                        <h2>Derniers Voyages</h2>
                        <a class="dash-card-pill brand" href="{{ route('trips.create') }}">+ Nouveau</a>
                    </div>
                    <div class="dash-list">
                        @forelse ($recentTrips->take(4) as $trip)
                            <div class="dash-list-item">
                                <div class="list-item-left">
                                    <div class="list-item-icon bus">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.9 2 11.2 2 11.5V16c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg>
                                    </div>
                                    <div class="list-item-text">
                                        <strong>{{ $trip->client->first_name }} {{ $trip->client->last_name }}</strong>
                                        <small>{{ $trip->travelRoute->departure_city }} ➔ {{ $trip->travelRoute->arrival_city }}</small>
                                    </div>
                                </div>
                                <span class="status active" style="font-size: 10px;">{{ $trip->travel_date->format('d/m') }}</span>
                            </div>
                        @empty
                            <p style="text-align: center; color: #94A3B8; font-size: 13px; margin: 20px 0;">Aucun voyage récent enregistré.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Bottom Row : Recent Parcels, Subscription Gauge, Desk Activity Widget -->
            <div class="dash-row-grid">
                <!-- Card 1: Recent Parcels (Team / Logistics) -->
                <div class="dash-card">
                    <div class="dash-card-head">
                        <h2>Colis & Expéditions</h2>
                        <a class="dash-card-pill brand" href="{{ route('parcels.create') }}">+ Nouveau Colis</a>
                    </div>
                    <div class="dash-list">
                        @forelse ($recentParcels as $parcel)
                            <div class="dash-list-item">
                                <div class="list-item-left">
                                    <div class="list-item-icon parcel">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path></svg>
                                    </div>
                                    <div class="list-item-text">
                                        <strong>{{ $parcel->receiver_name }}</strong>
                                        <small>{{ $parcel->tracking_code }} • {{ $parcel->originAgency?->city ?? 'Agence' }} ➔ {{ $parcel->destinationAgency?->city ?? 'Agence' }}</small>
                                    </div>
                                </div>
                                <span class="status {{ $parcel->status === 'delivered' ? 'active' : 'pending' }}" style="font-size: 10px;">
                                    {{ $parcel->status === 'delivered' ? 'Livré' : 'En transit' }}
                                </span>
                            </div>
                        @empty
                            <p style="text-align: center; color: #94A3B8; font-size: 13px; margin: 20px 0;">Aucun colis enregistré.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Card 2: Subscription Utilization Gauge -->
                <div class="dash-card">
                    <div class="dash-card-head">
                        <h2>Consommation Forfaits</h2>
                        <span class="dash-card-pill brand">VIP Club</span>
                    </div>
                    <div class="gauge-container">
                        <div class="gauge-svg-wrap">
                            <svg width="220" height="120" viewBox="0 0 220 120">
                                <defs>
                                    <linearGradient id="gaugeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#D90429" />
                                        <stop offset="100%" stop-color="#8B0018" />
                                    </linearGradient>
                                </defs>
                                <!-- Background Arc -->
                                <path d="M 20 110 A 90 90 0 0 1 200 110" fill="none" stroke="#E2E8F0" stroke-width="18" stroke-linecap="round" />
                                <!-- Dynamic Progress Arc -->
                                @php
                                    $circumference = 282.74; // Pi * radius (90)
                                    $dashOffset = $circumference * (1 - ($subscriptionStats['utilization_rate'] / 100));
                                @endphp
                                <path d="M 20 110 A 90 90 0 0 1 200 110" fill="none" stroke="url(#gaugeGrad)" stroke-width="18" stroke-linecap="round" stroke-dasharray="282.74" stroke-dashoffset="{{ $dashOffset }}" style="transition: stroke-dashoffset 0.8s ease;" />
                            </svg>
                            <div class="gauge-center-text">
                                <div class="gauge-pct">{{ $subscriptionStats['utilization_rate'] }}%</div>
                                <div class="gauge-sub">Voyages Utilisés</div>
                            </div>
                        </div>
                        <div class="gauge-legend">
                            <div class="legend-item">
                                <div class="legend-dot consumed"></div>
                                Consommés ({{ $subscriptionStats['consumed_trips'] }})
                            </div>
                            <div class="legend-item">
                                <div class="legend-dot remaining"></div>
                                Restants ({{ $subscriptionStats['remaining_trips'] }})
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Dark Activity Widget (Activité des Agents & Guichets) -->
                <div class="dark-activity-widget">
                    <div class="dark-activity-head">
                        <span>Activité des Guichets</span>
                        <div class="pulse-badge">
                            <div class="pulse-dot"></div>
                            En direct
                        </div>
                    </div>
                    <div class="dark-activity-main">
                        <div class="dark-activity-counter">{{ sprintf('%02d', $deskActivity['total_ops']) }}</div>
                        <div class="dark-activity-label">{{ $deskActivity['user_agency'] }} • Opérations traitées aujourd'hui</div>
                    </div>
                    <div class="dark-activity-breakdown">
                        <div class="breakdown-col">
                            <small>Voyages</small>
                            <strong>{{ $deskActivity['today_trips'] }}</strong>
                        </div>
                        <div class="breakdown-col">
                            <small>Colis</small>
                            <strong>{{ $deskActivity['today_parcels'] }}</strong>
                        </div>
                        <div class="breakdown-col">
                            <small>Paiements</small>
                            <strong>{{ $deskActivity['today_payments'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <section class="client-placeholder">
                <p>Consultez bientôt votre carte, votre solde et votre historique de voyages depuis cet espace.</p>
            </section>
        @endif
    </main>
</div>
@endsection
