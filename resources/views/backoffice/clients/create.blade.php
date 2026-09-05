@extends('layouts.app', ['title' => 'Nouveau client VIP - Buca Voyages VIP'])
@section('body')
<div class="shell">
    @include('layouts.navigation')
    <main class="office-content narrow">
        <div class="page-head"><div><p class="section-tag">Clients VIP</p><h1>Nouveau client</h1><p>Enregistrez les informations necessaires avant la creation d'une carte.</p></div></div>
        <form class="editor" method="POST" action="{{ route('vip-clients.store') }}">@include('backoffice.clients._form')</form>
    </main>
</div>
@endsection
