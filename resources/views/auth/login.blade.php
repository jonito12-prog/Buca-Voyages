@extends('layouts.app', ['title' => 'Connexion - Buca Voyages VIP'])

@section('body')
<main class="auth-page">
    <section class="auth-visual">
        <div class="brand">BUCA VOYAGES VIP</div>
        <div>
            <h1>Gestion securisee des cartes VIP et forfaits de voyages.</h1>
            <p>Acces reserve aux equipes autorisees pour suivre les clients fideles, les cartes, les consommations et les paiements.</p>
        </div>
    </section>

    <section class="auth-panel">
        <div style="margin-bottom: 24px;">
            <img src="https://bucavoyages.cm/storage/settings/logo/01KCRPJWJ1463PAXKS62E9ZFDC.png" alt="Buca Voyages" style="height: 48px; width: auto; max-width: 100%;">
        </div>
        <h2>Connexion</h2>
        <p class="lead">Entrez vos identifiants pour acceder a l'espace de gestion.</p>

        @if (session('status'))
            <div class="alert ok">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>

            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>

            <label class="check">
                <input type="checkbox" name="remember" value="1">
                <span>Rester connecte</span>
            </label>

            <button class="primary" type="submit">Se connecter</button>
        </form>

        <div class="topline">
            <a href="{{ route('password.request') }}">Mot de passe oublie ?</a>
            <a href="{{ route('register') }}">Creer un compte</a>
        </div>
    </section>
</main>
@endsection

