<div class="form-grid">
    <label class="wide"><span>Nom de la formule *</span><input type="text" name="name" value="{{ old('name', $package->name) }}" required></label>
    <label><span>Type de periode *</span>
        <select name="period_type" required>
            @foreach (['weekly' => 'Hebdomadaire', 'monthly' => 'Mensuel', 'annual' => 'Annuel', 'custom' => 'Personnalise'] as $value => $label)
                <option value="{{ $value }}" @selected(old('period_type', $package->period_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label><span>Statut *</span>
        <select name="status" required>
            <option value="active" @selected(old('status', $package->status) === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $package->status) === 'inactive')>Inactive</option>
        </select>
    </label>
    <label><span>Nombre de voyages *</span><input type="number" name="trip_count" min="1" value="{{ old('trip_count', $package->trip_count) }}" required></label>
    <label><span>Duree de validite (jours) *</span><input type="number" name="validity_days" min="1" value="{{ old('validity_days', $package->validity_days) }}" required></label>
    <label><span>Prix (FCFA) *</span><input type="number" name="price" min="0" step="1" value="{{ old('price', $package->price) }}" required></label>
    <label><span>Classe *</span>
        <select name="class_type" required>
            @foreach (['vip' => 'VIP', 'classique' => 'Classique', 'mixed' => 'Mixte'] as $value => $label)
                <option value="{{ $value }}" @selected(old('class_type', $package->class_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="wide"><span>Trajet lie (optionnel)</span>
        <select name="travel_route_id">
            <option value="">Tous trajets</option>
            @foreach ($routes as $route)
                <option value="{{ $route->id }}" @selected(old('travel_route_id', $package->travel_route_id) == $route->id)>{{ $route->departure_city }} → {{ $route->arrival_city }}</option>
            @endforeach
        </select>
    </label>
</div>
