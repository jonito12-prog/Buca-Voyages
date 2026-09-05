<header class="bar">
    @if (auth()->check() && in_array(auth()->user()->role?->slug, ['admin', 'agent'], true))
        <div class="brand-line">
            <strong>Buca Voyages VIP</strong>
            @if (auth()->user()->hasPermission('dashboard.view'))
                <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>Accueil</a>
            @endif
            @if (auth()->user()->hasPermission('vip_clients.manage'))
                <a href="{{ route('vip-clients.index') }}" @class(['active' => request()->routeIs('vip-clients.*') || request()->routeIs('vip-cards.*')])>Clients VIP</a>
            @endif
            @if (auth()->user()->hasPermission('trips.consume'))
                <a href="{{ route('trips.index') }}" @class(['active' => request()->routeIs('trips.*')])>Voyages</a>
            @endif
            @if (auth()->user()->hasPermission('parcels.manage') || auth()->user()->hasPermission('parcels.view'))
                <a href="{{ route('parcels.index') }}" @class(['active' => request()->routeIs('parcels.*')])>Colis</a>
            @endif
            @if (auth()->user()->hasPermission('payments.view'))
                <a href="{{ route('payments.index') }}" @class(['active' => request()->routeIs('payments.*')])>Paiements</a>
            @endif
            @if (auth()->user()->hasPermission('roles.manage') || auth()->user()->hasPermission('users.manage'))
                <a href="{{ route('security.index') }}" @class(['active' => request()->routeIs('security.*')])>Sécurité</a>
            @endif
        </div>
    @else
        <div><strong>Buca Voyages VIP</strong> <span>Espace client</span></div>
    @endif
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="logout" type="submit">Déconnexion</button>
    </form>
</header>
