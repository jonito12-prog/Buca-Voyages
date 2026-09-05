@extends('layouts.app', ['title' => 'Modifier client VIP - Buca Voyages VIP'])
@section('body')
<div class="shell">
    @include('layouts.navigation')
    <main class="office-content narrow">
        <div class="page-head"><div><p class="section-tag">Clients VIP</p><h1>Modifier le client</h1><p>{{ $vipClient->first_name }} {{ $vipClient->last_name }}</p></div></div>
        <form class="editor" method="POST" action="{{ route('vip-clients.update', $vipClient) }}">@include('backoffice.clients._form')</form>
    </main>
</div>
@endsection
