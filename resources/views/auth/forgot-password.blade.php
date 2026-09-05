@extends('layouts.app', ['title' => 'Mot de passe oublie - Buca Voyages VIP'])

@section('body')
<main class="auth-page">
    <section class="auth-visual">
        <div class="brand">BUCA VOYAGES VIP</div>
        <div>
            <h1>Reinitialisation securisee du mot de passe.</h1>
            <p>Le lien envoye expire automatiquement afin de proteger les comptes de l'agence.</p>
        </div>
    </section>

    <section class="auth-panel">
        <div style="margin-bottom: 24px;">
            <img src="https://bucavoyages.cm/storage/settings/logo/01KCRPJWJ1463PAXKS62E9ZFDC.png" alt="Buca Voyages" style="height: 48px; width: auto; max-width: 100%;">
        </div>
        <h2>Mot de passe oublie</h2>
        <p class="lead">Indiquez votre email professionnel. Un lien de reinitialisation sera genere.</p>

        @if (session('status'))
            <div class="alert ok">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            <button class="primary" type="submit" style="margin-top:18px">Envoyer le lien</button>
        </form>

        <div class="topline">
            <a href="{{ route('login') }}">Retour a la connexion</a>
        </div>
    </section>
</main>
@endsection
