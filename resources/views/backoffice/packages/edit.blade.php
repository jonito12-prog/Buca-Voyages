@extends('layouts.app', ['title' => 'Modifier formule - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')
    <main class="office-content narrow">
        <div class="page-head"><div><p class="section-tag">Forfait</p><h1>Modifier {{ $package->name }}</h1></div></div>
        <form class="editor" method="POST" action="{{ route('packages.update', $package) }}">@csrf @method('PUT')
            @include('backoffice.packages._form')
            @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
            <div class="form-buttons"><button class="primary action-button" type="submit">Mettre a jour</button><a class="secondary-link" href="{{ route('packages.index') }}">Annuler</a></div>
        </form>
    </main>
</div>
@endsection
