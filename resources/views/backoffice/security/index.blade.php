@extends('layouts.app', ['title' => 'Sécurité & Contrôle d\'accès - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content">
        @if (session('status'))
            <div class="alert ok">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert error">{{ $errors->first() }}</div>
        @endif

        <div class="page-head">
            <div>
                <p class="section-tag">Sécurité & Gouvernance</p>
                <h1>Contrôle d'accès (RBAC)</h1>
                <p>Gérez les modules applicatifs, les privilèges de sécurité, les rôles d'utilisateurs et l'attribution des habilitations.</p>
            </div>
            @if(auth()->user()->hasPermission('roles.manage'))
                <a class="command primary-link" href="{{ route('security.roles.create') }}">Nouveau rôle</a>
            @endif
        </div>

        <div class="tabs-container" style="margin-bottom: 24px;">
            <div class="tab-buttons" style="display: flex; gap: 8px; border-bottom: 1px solid var(--line); padding-bottom: 1px;">
                <button class="tab-btn active" onclick="switchTab('roles-tab')" id="roles-btn" style="background: none; border: none; padding: 12px 20px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 15px; color: var(--muted); border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.2s;">
                    Rôles & Permissions
                </button>
                <button class="tab-btn" onclick="switchTab('modules-tab')" id="modules-btn" style="background: none; border: none; padding: 12px 20px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 15px; color: var(--muted); border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.2s;">
                    Privilèges par Module
                </button>
                <button class="tab-btn" onclick="switchTab('users-tab')" id="users-btn" style="background: none; border: none; padding: 12px 20px; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 15px; color: var(--muted); border-bottom: 2px solid transparent; cursor: pointer; transition: all 0.2s;">
                    Habilitations Utilisateurs
                </button>
            </div>
        </div>

        <!-- TAB: Roles -->
        <div id="roles-tab" class="tab-content">
            <section class="data-panel">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nom du rôle</th>
                            <th>Description</th>
                            <th>Privilèges attribués</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td style="width: 200px;">
                                    <strong>{{ $role->name }}</strong>
                                    <span class="status" style="font-size: 10px; padding: 1px 6px; margin-top: 4px;">{{ $role->slug }}</span>
                                </td>
                                <td>{{ $role->description ?: 'Aucune description' }}</td>
                                <td style="max-width: 400px; line-height: 1.6;">
                                    @if ($role->slug === 'admin')
                                        <span class="status active" style="font-size: 10px; background: #e3f2fd; color: #0d47a1; border-color: #bbdefb;">TOUS LES PRIVILÈGES (Administrateur)</span>
                                    @else
                                        @forelse ($role->permissions as $perm)
                                            <span class="status" style="font-size: 10px; margin: 2px; background: #f1f3f5; color: #495057; border-color: #dee2e6;" title="{{ $perm->name }} ({{ $perm->module }})">
                                                {{ $perm->slug }}
                                            </span>
                                        @empty
                                            <span style="color: var(--muted); font-size: 12px;">Aucun privilège</span>
                                        @endforelse
                                    @endif
                                </td>
                                <td class="actions" style="text-align: right; vertical-align: middle;">
                                    @if(auth()->user()->hasPermission('roles.manage'))
                                        @if (!in_array($role->slug, ['admin', 'client'], true))
                                            <a href="{{ route('security.roles.edit', $role) }}">Modifier</a>
                                            
                                            @if (!in_array($role->slug, ['agent'], true))
                                                <form action="{{ route('security.roles.destroy', $role) }}" method="POST" class="inline-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="color: var(--danger); background: none; border: none; font-weight: 600; font-family: 'Poppins', sans-serif; font-size: 13px; cursor: pointer; padding: 0; margin-left: 10px;">Supprimer</button>
                                                </form>
                                            @endif
                                        @else
                                            <span style="color: var(--muted); font-size: 12px; font-style: italic;">Système</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </div>

        <!-- TAB: Privileges by Module -->
        <div id="modules-tab" class="tab-content" style="display: none;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px;">
                @foreach ($permissionsGrouped as $module => $permissions)
                    <div class="detail-panel" style="display: flex; flex-direction: column; height: 100%;">
                        <h2 style="border-bottom: 2px solid var(--brand); padding-bottom: 8px; margin-bottom: 12px; font-size: 16px; color: var(--ink);">
                            Module: {{ $module }}
                        </h2>
                        <div style="display: flex; flex-direction: column; gap: 8px; flex-grow: 1;">
                            @foreach ($permissions as $permission)
                                <div style="padding: 10px; background: var(--soft); border-radius: 6px; border: 1px solid var(--line);">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <strong style="font-size: 14px; color: var(--ink);">{{ $permission->name }}</strong>
                                    </div>
                                    <code style="font-size: 12px; color: var(--brand-dark); font-weight: 600;">{{ $permission->slug }}</code>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAB: Users Habilitations -->
        <div id="users-tab" class="tab-content" style="display: none;">
            <section class="data-panel">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nom complet</th>
                            <th>Email & Téléphone</th>
                            <th>Agence d'affectation</th>
                            <th>Rôle attribué</th>
                            @if(auth()->user()->hasPermission('users.manage'))
                                <th style="text-align: right;">Validation</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    <small>Inscrit le : {{ $user->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $user->email }}</strong>
                                    <small>{{ $user->phone ?: 'Aucun numéro' }}</small>
                                </td>
                                <form action="{{ route('security.users.update-role', $user) }}" method="POST">
                                    @csrf
                                    <td>
                                        @if(auth()->user()->hasPermission('users.manage') && $user->id !== auth()->user()->id)
                                            <select name="agency_id" style="height: 38px; padding: 0 8px; font-size: 13px;">
                                                <option value="">Aucune agence (Siège / Client)</option>
                                                @foreach ($agencies as $agency)
                                                    <option value="{{ $agency->id }}" @selected($user->agency_id === $agency->id)>
                                                        {{ $agency->city }} - {{ $agency->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <span>{{ $user->agency ? $user->agency->city . ' - ' . $user->agency->name : 'Aucune agence' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(auth()->user()->hasPermission('users.manage') && $user->id !== auth()->user()->id)
                                            <select name="role_id" style="height: 38px; padding: 0 8px; font-size: 13px;">
                                                <option value="">Aucun rôle</option>
                                                @foreach ($allRoles as $role)
                                                    <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>
                                                        {{ $role->name }} ({{ $role->slug }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <span class="status active" style="font-weight: 700;">{{ $user->role ? $user->role->name : 'Aucun rôle' }}</span>
                                        @endif
                                    </td>
                                    @if(auth()->user()->hasPermission('users.manage'))
                                        <td style="text-align: right; vertical-align: middle;">
                                            @if ($user->id !== auth()->user()->id)
                                                <button type="submit" class="command compact filter-button" style="margin-top: 0; min-height: 38px; padding: 0 16px; height: 38px; font-size: 13px;">
                                                    Enregistrer
                                                </button>
                                            @else
                                                <span style="color: var(--muted); font-size: 12px; font-style: italic;">Votre session</span>
                                            @endif
                                        </td>
                                    @endif
                                </form>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">Aucun utilisateur trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
            <div class="pagination">{{ $users->links() }}</div>
        </div>
    </main>
</div>

<script>
    function switchTab(tabId) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(function(content) {
            content.style.display = 'none';
        });

        // Show selected tab content
        document.getElementById(tabId).style.display = 'block';

        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.classList.remove('active');
            btn.style.color = 'var(--muted)';
            btn.style.borderBottomColor = 'transparent';
        });

        // Add active class to clicked button
        var activeBtn = document.getElementById(tabId.split('-')[0] + '-btn');
        activeBtn.classList.add('active');
        activeBtn.style.color = 'var(--brand)';
        activeBtn.style.borderBottomColor = 'var(--brand)';
    }

    // Set initial tab styling
    document.addEventListener("DOMContentLoaded", function() {
        var activeBtn = document.getElementById('roles-btn');
        activeBtn.style.color = 'var(--brand)';
        activeBtn.style.borderBottomColor = 'var(--brand)';
        
        // Handle pagination URL hash or page parameter for users tab
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('users_page')) {
            switchTab('users-tab');
        }
    });
</script>
@endsection
