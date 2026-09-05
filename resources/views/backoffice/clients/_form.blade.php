@csrf
@if(isset($vipClient)) @method('PUT') @endif
<div class="form-grid">
    <label><span>Prenom *</span><input type="text" name="first_name" value="{{ old('first_name', $vipClient->first_name ?? $prefillFirstName ?? '') }}" required></label>
    <label><span>Nom *</span><input type="text" name="last_name" value="{{ old('last_name', $vipClient->last_name ?? $prefillLastName ?? '') }}" required></label>
    <label><span>Telephone *</span><input type="text" name="phone" value="{{ old('phone', $vipClient->phone ?? $prefillPhone ?? '') }}" required></label>
    <label><span>Email</span><input type="email" name="email" value="{{ old('email', $vipClient->email ?? $prefillEmail ?? '') }}"></label>
    <label><span>Sexe</span><select name="gender"><option value="">Non renseigne</option><option value="F" @selected(old('gender', $vipClient->gender ?? '') === 'F')>Femme</option><option value="M" @selected(old('gender', $vipClient->gender ?? '') === 'M')>Homme</option><option value="Autre" @selected(old('gender', $vipClient->gender ?? '') === 'Autre')>Autre</option></select></label>
    <label><span>Date de naissance</span><input type="date" name="birth_date" value="{{ old('birth_date', isset($vipClient) && $vipClient->birth_date ? $vipClient->birth_date->format('Y-m-d') : '') }}"></label>
    @php
        $knownTypes = ['CNI', 'Passeport', 'Permis'];
        $dbType = $vipClient->identity_type ?? '';
        $selectedType = old('identity_type', $dbType);
        $isOther = !empty($selectedType) && !in_array($selectedType, $knownTypes);
        $otherValue = $isOther ? $selectedType : 'Récépissé';
    @endphp
    <label><span>Type de piece</span>
        <select name="identity_type" id="identity_type_select">
            <option value="">Non renseigne</option>
            <option value="CNI" @selected(old('identity_type', $dbType) === 'CNI')>CNI</option>
            <option value="Passeport" @selected(old('identity_type', $dbType) === 'Passeport')>Passeport</option>
            <option value="Permis" @selected(old('identity_type', $dbType) === 'Permis')>Permis</option>
            <option value="Autre" @selected($isOther || old('identity_type') === 'Autre')>Autre</option>
        </select>
    </label>
    <label id="identity_type_other_container" style="display: {{ ($isOther || old('identity_type') === 'Autre') ? 'block' : 'none' }};">
        <span>Préciser le type (ex: Récépissé)</span>
        <input type="text" name="identity_type_other" id="identity_type_other" value="{{ old('identity_type_other', $otherValue) }}">
    </label>
    <label><span>Numero de piece</span><input type="text" name="identity_number" value="{{ old('identity_number', $vipClient->identity_number ?? '') }}"></label>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('identity_type_select');
            const container = document.getElementById('identity_type_other_container');
            const input = document.getElementById('identity_type_other');
            
            if (select && container && input) {
                select.addEventListener('change', function() {
                    if (this.value === 'Autre') {
                        container.style.display = 'block';
                        if (!input.value.trim()) {
                            input.value = 'Récépissé';
                        }
                    } else {
                        container.style.display = 'none';
                    }
                });
            }
        });
    </script>
    <label class="wide"><span>Adresse</span><input type="text" name="address" value="{{ old('address', $vipClient->address ?? '') }}"></label>
    <label><span>Statut *</span><select name="status" required><option value="active" @selected(old('status', $vipClient->status ?? 'active') === 'active')>Actif</option><option value="suspended" @selected(old('status', $vipClient->status ?? '') === 'suspended')>Suspendu</option><option value="archived" @selected(old('status', $vipClient->status ?? '') === 'archived')>Archive</option></select></label>
    
    <div class="wide" style="margin: 20px 0; border-bottom: 1px dashed var(--line);"></div>

    <div class="wide">
        <h3 style="margin: 0 0 15px; font-size: 16px; font-family: 'Poppins', sans-serif;">Accès Espace Client</h3>
        @php
            $emailToCheck = old('email', $vipClient->email ?? $prefillEmail ?? '');
            $existingUser = !empty($emailToCheck) ? \App\Models\User::where('email', $emailToCheck)->whereHas('role', fn($q) => $q->where('slug', 'client'))->first() : null;
        @endphp
        @if((isset($vipClient) && $vipClient->user_id) || $existingUser)
            <p style="color: #1a73e8; background: #e8f0fe; border: 1px solid #d2e3fc; padding: 10px 14px; border-radius: 6px; font-size: 14px; margin-bottom: 12px; font-weight: 500;">
                ✓ Ce client possède un compte en ligne enregistré (<strong>{{ $vipClient->user->email ?? $existingUser->email }}</strong>).
            </p>
            @if(isset($vipClient) && $vipClient->user_id)
                <label><span>Changer le mot de passe (Optionnel)</span>
                    <input type="password" name="password" placeholder="Saisissez un nouveau mot de passe pour le modifier">
                </label>
            @endif
        @else
            <label class="check" style="margin-bottom: 12px;">
                <input type="checkbox" name="create_user_account" value="1" id="create_user_account" @checked(old('create_user_account'))>
                <span>Créer un compte utilisateur en ligne (nécessite un e-mail ci-dessus)</span>
            </label>
            <div id="password-container" style="display: {{ old('create_user_account') ? 'block' : 'none' }}; margin-top: 12px;">
                <label><span>Mot de passe temporaire *</span>
                    <input type="password" name="password" id="password-field" placeholder="Minimum 8 caractères">
                </label>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const checkbox = document.getElementById('create_user_account');
                    const container = document.getElementById('password-container');
                    const field = document.getElementById('password-field');
                    
                    if (checkbox && container && field) {
                        checkbox.addEventListener('change', function() {
                            container.style.display = this.checked ? 'block' : 'none';
                            field.required = this.checked;
                        });
                        
                        // Initial setup
                        container.style.display = checkbox.checked ? 'block' : 'none';
                        field.required = checkbox.checked;
                    }
                });
            </script>
        @endif
    </div>
</div>
@if ($errors->any())<div class="alert error"><strong>Veuillez corriger :</strong> {{ $errors->first() }}</div>@endif
<div class="form-buttons">
    <button class="primary action-button" type="submit">{{ isset($vipClient) ? 'Enregistrer' : 'Creer le client' }}</button>
    <a class="secondary-link" href="{{ isset($vipClient) ? route('vip-clients.show', $vipClient) : route('vip-clients.index') }}">Annuler</a>
</div>
