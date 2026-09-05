@extends('layouts.app', ['title' => 'Inscription - Buca Voyages VIP'])

@section('body')
<main class="auth-page">
    <section class="auth-visual">
        <div class="brand">BUCA VOYAGES VIP</div>
        <div>
            <h1>Creation d'un compte client VIP.</h1>
            <p>L'inscription publique cree un acces client. Les comptes agents et administrateurs restent controles par l'agence.</p>
        </div>
    </section>

    <section class="auth-panel">
        <div style="margin-bottom: 24px;">
            <img src="https://bucavoyages.cm/storage/settings/logo/01KCRPJWJ1463PAXKS62E9ZFDC.png" alt="Buca Voyages" style="height: 48px; width: auto; max-width: 100%;">
        </div>
        <h2>Inscription</h2>
        <p class="lead">Creez votre compte pour acceder a l'espace securise.</p>

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <label for="name">Nom complet</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>

            <label for="phone">Telephone</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}">

            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" required>

            <label for="password_confirmation">Confirmation</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>

            <button class="primary" type="submit" style="margin-top:18px">Creer mon compte</button>
        </form>

        <div class="topline">
            <a href="{{ route('login') }}">J'ai deja un compte</a>
        </div>
    </section>
</main>
@endsection
