@extends('layouts.app', ['title' => 'Nouveau mot de passe - Buca Voyages VIP'])

@section('body')
<main class="auth-page">
    <section class="auth-visual">
        <div class="brand">BUCA VOYAGES VIP</div>
        <div>
            <h1>Choisissez un nouveau mot de passe.</h1>
            <p>Utilisez au moins 8 caracteres et evitez les mots de passe deja utilises ailleurs.</p>
        </div>
    </section>

    <section class="auth-panel">
        <div style="margin-bottom: 24px;">
            <img src="https://bucavoyages.cm/storage/settings/logo/01KCRPJWJ1463PAXKS62E9ZFDC.png" alt="Buca Voyages" style="height: 48px; width: auto; max-width: 100%;">
        </div>
        <h2>Nouveau mot de passe</h2>
        <p class="lead">Finalisez la reinitialisation de votre compte.</p>

        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required>

            <label for="password">Nouveau mot de passe</label>
            <input id="password" type="password" name="password" required>

            <label for="password_confirmation">Confirmation</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>

            <button class="primary" type="submit" style="margin-top:18px">Modifier le mot de passe</button>
        </form>
    </section>
</main>
@endsection
