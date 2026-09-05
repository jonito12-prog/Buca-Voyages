@extends('layouts.app', ['title' => 'Verifier votre email - Buca Voyages VIP'])

@section('body')
<main class="auth-page">
    <section class="auth-visual">
        <div class="brand">BUCA VOYAGES VIP</div>
        <div>
            <h1>Confirmez votre adresse email.</h1>
            <p>Cette verification protege votre compte et permettra les operations liees a votre carte VIP.</p>
        </div>
    </section>

    <section class="auth-panel">
        <div style="margin-bottom: 24px;">
            <img src="https://bucavoyages.cm/storage/settings/logo/01KCRPJWJ1463PAXKS62E9ZFDC.png" alt="Buca Voyages" style="height: 48px; width: auto; max-width: 100%;">
        </div>
        <h2>Verification requise</h2>
        <p class="lead">Un lien de validation a ete envoye a votre adresse email. Ouvrez-le pour activer votre compte client.</p>

        @if (session('status'))
            <div class="alert ok">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="primary" type="submit">Renvoyer le lien</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" style="margin-top:14px">
            @csrf
            <button class="logout" type="submit" style="width:100%">Se deconnecter</button>
        </form>
    </section>
</main>
@endsection
