@extends('layouts.app', ['title' => 'Enregistrer un colis - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content narrow">
        <div class="page-head">
            <div>
                <p class="section-tag">Messagerie</p>
                <h1>Enregistrer un envoi de colis</h1>
                <p>Saisissez les informations d'expédition du colis.</p>
            </div>
        </div>

        <form class="editor" method="POST" action="{{ route('parcels.store') }}" id="parcel-form">
            @csrf

            <div class="form-grid">
                <!-- Expéditeur VIP -->
                <label class="wide">
                    <span>Expéditeur VIP (Optionnel)</span>
                    <select name="sender_vip_client_id" id="sender_vip_client_id">
                        <option value="">-- Client non-VIP (Envoi standard) --</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" 
                                    data-name="{{ $client->first_name }} {{ $client->last_name }}"
                                    data-phone="{{ $client->phone }}"
                                    data-email="{{ $client->email }}"
                                    @selected(old('sender_vip_client_id', $vipClient?->id) == $client->id)>
                                {{ $client->first_name }} {{ $client->last_name }} ({{ $client->phone }})
                            </option>
                        @endforeach
                    </select>
                </label>

                <!-- Infos Expéditeur -->
                <label>
                    <span>Nom de l'expéditeur *</span>
                    <input type="text" name="sender_name" id="sender_name" value="{{ old('sender_name') }}" required>
                </label>

                <label>
                    <span>Téléphone de l'expéditeur *</span>
                    <input type="text" name="sender_phone" id="sender_phone" value="{{ old('sender_phone') }}" required>
                </label>

                <label class="wide">
                    <span>Adresse E-mail de l'expéditeur (Optionnel, pour alerte de livraison)</span>
                    <input type="email" name="sender_email" id="sender_email" value="{{ old('sender_email') }}" placeholder="Ex: expediteur@gmail.com">
                </label>

                <div class="wide" style="margin: 10px 0; border-bottom: 1px dashed var(--line);"></div>

                <!-- Infos Destinataire -->
                <label>
                    <span>Nom du destinataire *</span>
                    <input type="text" name="receiver_name" value="{{ old('receiver_name') }}" required placeholder="Nom complet">
                </label>

                <label>
                    <span>Téléphone du destinataire *</span>
                    <input type="text" name="receiver_phone" value="{{ old('receiver_phone') }}" required placeholder="Ex: 6xx xx xx xx">
                </label>

                <div class="wide" style="margin: 10px 0; border-bottom: 1px dashed var(--line);"></div>

                <!-- Agences d'expédition -->
                <label>
                    <span>Agence d'origine *</span>
                    <select name="origin_agency_id" id="origin_agency_id" required>
                        <option value="">Choisir</option>
                        @foreach ($agencies as $agency)
                            <option value="{{ $agency->id }}" @selected(old('origin_agency_id') == $agency->id)>
                                {{ $agency->name }} — {{ $agency->city }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Agence de destination *</span>
                    <select name="destination_agency_id" id="destination_agency_id" required>
                        <option value="">Choisir</option>
                        @foreach ($agencies as $agency)
                            <option value="{{ $agency->id }}" @selected(old('destination_agency_id') == $agency->id)>
                                {{ $agency->name }} — {{ $agency->city }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <!-- Catégorie de colis -->
                <label>
                    <span>Catégorie de colis / Volume *</span>
                    <select name="size_category" id="size_category" required>
                        <option value="document" data-price="1500" @selected(old('size_category') === 'document')>Enveloppe / Document</option>
                        <option value="small" data-price="2500" @selected(old('size_category') === 'small')>Petit colis</option>
                        <option value="medium" data-price="4000" @selected(old('size_category', 'medium') === 'medium')>Colis Moyen</option>
                        <option value="large" data-price="6000" @selected(old('size_category') === 'large')>Grand carton / Sac</option>
                        <option value="special" data-price="10000" @selected(old('size_category') === 'special')>Électroménager / Spécial</option>
                    </select>
                </label>

                <!-- Valeur Déclarée -->
                <label>
                    <span>Valeur déclarée (FCFA)</span>
                    <input type="number" name="declared_value" value="{{ old('declared_value', 0) }}" min="0">
                </label>

                <!-- Prix d'envoi -->
                <label>
                    <span>Prix de transport (FCFA) *</span>
                    <input type="number" name="shipping_price" id="shipping_price" value="{{ old('shipping_price', 4000) }}" min="0" required>
                </label>

                <!-- Méthode de paiement -->
                <label>
                    <span>Mode de paiement *</span>
                    <select name="payment_method" id="payment_method" required>
                        <option value="cash" @selected(old('payment_method') === 'cash')>Espèces (Cash)</option>
                        <option value="momo" @selected(old('payment_method') === 'momo')>MTN MoMo</option>
                        <option value="orange_money" @selected(old('payment_method') === 'orange_money')>Orange Money</option>
                    </select>
                </label>

                <label class="wide">
                    <span>Description du contenu / Notes</span>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Ex: Contient des vêtements, 1 sac de riz. Optionnel.">
                </label>
            </div>

            @if ($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif

            <div class="form-buttons">
                <button class="primary action-button" type="submit">Valider l'expédition</button>
                <a class="secondary-link" href="{{ route('parcels.index') }}">Annuler</a>
            </div>
        </form>
    </main>
</div>

<script>
(function () {
    const senderVipSelect = document.getElementById('sender_vip_client_id');
    const senderNameInput = document.getElementById('sender_name');
    const senderPhoneInput = document.getElementById('sender_phone');
    const senderEmailInput = document.getElementById('sender_email');
    
    const sizeCategorySelect = document.getElementById('size_category');
    const shippingPriceInput = document.getElementById('shipping_price');

    // Prefill Sender Info when VIP Client is selected
    function handleVipClientChange() {
        const selectedOption = senderVipSelect.options[senderVipSelect.selectedIndex];
        const vipClientId = senderVipSelect.value;

        if (vipClientId) {
            senderNameInput.value = selectedOption.dataset.name || '';
            senderPhoneInput.value = selectedOption.dataset.phone || '';
            senderEmailInput.value = selectedOption.dataset.email || '';
        } else {
            senderNameInput.value = '';
            senderPhoneInput.value = '';
            senderEmailInput.value = '';
        }
    }

    // Update suggested price based on Size Category
    function handleSizeCategoryChange() {
        const selectedOption = sizeCategorySelect.options[sizeCategorySelect.selectedIndex];
        if (!selectedOption) return;

        const suggestedPrice = selectedOption.dataset.price;

        // Auto prefill price (the user can still change it manually)
        shippingPriceInput.value = suggestedPrice;
    }

    // Event listeners
    senderVipSelect.addEventListener('change', handleVipClientChange);
    sizeCategorySelect.addEventListener('change', handleSizeCategoryChange);

    // Initial setup on page load
    if (senderVipSelect.value) {
        const selectedOption = senderVipSelect.options[senderVipSelect.selectedIndex];
        senderNameInput.value = senderNameInput.value || selectedOption.dataset.name || '';
        senderPhoneInput.value = senderPhoneInput.value || selectedOption.dataset.phone || '';
        senderEmailInput.value = senderEmailInput.value || selectedOption.dataset.email || '';
    }

    handleSizeCategoryChange();

})();
</script>
@endsection
