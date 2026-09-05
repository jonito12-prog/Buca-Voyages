<section class="detail-panel affiliate-panel">
    <h2>Personnes autorisees a voyager</h2>
    <p class="confirm-lead">Si le titulaire ne peut pas voyager, il peut designer une personne affiliee qui consommera les voyages de sa carte.</p>
    @if ($vipClient->travelAffiliates->isEmpty())
        <p class="empty-block">Aucune personne affiliee pour le moment.</p>
    @else
        <table class="data-table compact-table">
            <thead><tr><th>Nom</th><th>Lien</th><th>Carte</th><th>Statut</th><th></th></tr></thead>
            <tbody>
                @foreach ($vipClient->travelAffiliates as $affiliate)
                    <tr>
                        <td><strong>{{ $affiliate->first_name }} {{ $affiliate->last_name }}</strong><small>{{ $affiliate->phone ?: 'Telephone non renseigne' }}</small></td>
                        <td>{{ $affiliate->relationship ?: '-' }}</td>
                        <td>{{ $affiliate->card?->card_number ?? 'Toutes cartes' }}</td>
                        <td><span class="status {{ $affiliate->status === 'active' ? 'active' : 'archived' }}">{{ $affiliate->status === 'active' ? 'Active' : 'Inactive' }}</span></td>
                        <td class="actions">
                            <form method="POST" action="{{ route('vip-clients.affiliates.toggle', [$vipClient, $affiliate]) }}">@csrf @method('PATCH')
                                <button type="submit">{{ $affiliate->status === 'active' ? 'Desactiver' : 'Reactiver' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <form class="editor affiliate-form" method="POST" action="{{ route('vip-clients.affiliates.store', $vipClient) }}">@csrf
        <div class="form-grid">
            <label><span>Prenom *</span><input type="text" name="first_name" value="{{ old('first_name') }}" required></label>
            <label><span>Nom *</span><input type="text" name="last_name" value="{{ old('last_name') }}" required></label>
            <label><span>Telephone</span><input type="text" name="phone" value="{{ old('phone') }}"></label>
            <label><span>Lien avec le titulaire</span>
                <select name="relationship">
                    <option value="">Choisir</option>
                    @foreach (['Conjoint', 'Enfant', 'Parent', 'Employe', 'Assistant', 'Autre'] as $relationship)
                        <option value="{{ $relationship }}" @selected(old('relationship') === $relationship)>{{ $relationship }}</option>
                    @endforeach
                </select>
            </label>
            <label class="wide"><span>Carte concernee</span>
                <select name="vip_card_id">
                    <option value="">Toutes les cartes du client</option>
                    @foreach ($vipClient->cards as $card)
                        <option value="{{ $card->id }}" @selected(old('vip_card_id') == $card->id)>{{ $card->card_number }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="form-buttons"><button class="primary action-button compact-btn" type="submit">Ajouter une personne affiliee</button></div>
    </form>
</section>
