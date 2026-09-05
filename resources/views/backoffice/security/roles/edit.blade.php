@extends('layouts.app', ['title' => 'Modifier le rôle - Buca Voyages VIP'])

@section('body')
<div class="shell">
    @include('layouts.navigation')

    <main class="office-content narrow">
        <div class="page-head">
            <div>
                <p class="section-tag">Sécurité & Gouvernance</p>
                <h1>Modifier le rôle : {{ $role->name }}</h1>
                <p>Modifiez le nom, la description ou les privilèges applicatifs de ce rôle.</p>
            </div>
        </div>

        <form class="editor" method="POST" action="{{ route('security.roles.update', $role) }}">
            @csrf
            @method('PUT')
            
            <div class="form-grid">
                <div class="wide">
                    <label><span>Nom du rôle *</span>
                        <input type="text" name="name" value="{{ old('name', $role->name) }}" placeholder="Ex: Chef d'agence, Superviseur colis" required>
                    </label>
                </div>
                
                <div class="wide">
                    <label><span>Description</span>
                        <input type="text" name="description" value="{{ old('description', $role->description) }}" placeholder="Ex: Dispose d'un accès complet aux opérations et aux validations financières.">
                    </label>
                </div>

                <div class="wide" style="margin-top: 14px;">
                    <span style="display:block; font-size:12px; margin-bottom:12px; color:var(--muted); font-weight:700; text-transform: uppercase; letter-spacing: 0.5px;">
                        Privilèges et modules d'accès *
                    </span>
                    
                    <div style="display:grid; grid-template-columns: 1fr; gap: 20px;">
                        @foreach ($permissionsGrouped as $module => $permissions)
                            <div style="background: var(--soft); border: 1px solid var(--line); border-radius: 8px; padding: 18px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--line); padding-bottom: 8px; margin-bottom: 12px;">
                                    <strong style="font-family: 'Poppins', sans-serif; font-size: 15px; color: var(--brand-dark);">
                                        {{ $module }}
                                    </strong>
                                    <button type="button" onclick="toggleModulePermissions('{{ Str::slug($module) }}')" style="background: none; border: none; font-size: 11px; font-weight: 700; color: var(--muted); cursor: pointer; padding: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                        Tout cocher / décocher
                                    </button>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px;">
                                    @foreach ($permissions as $permission)
                                        <label class="check" style="margin: 0; padding: 6px; background: white; border: 1px solid var(--line); border-radius: 6px; font-weight: 500; font-size: 13.5px; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s;">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="perm-checkbox-{{ Str::slug($module) }}" @checked(is_array(old('permissions')) ? in_array($permission->id, old('permissions')) : in_array($permission->id, $rolePermissionIds))>
                                            <div>
                                                <div style="font-weight: 600; color: var(--ink);">{{ $permission->name }}</div>
                                                <code style="font-size: 10.5px; color: var(--muted);">{{ $permission->slug }}</code>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert error" style="margin-top: 20px;">{{ $errors->first() }}</div>
            @endif

            <div class="form-buttons" style="margin-top: 30px;">
                <button class="primary action-button" type="submit">Enregistrer les modifications</button>
                <a class="secondary-link" href="{{ route('security.index') }}">Annuler</a>
            </div>
        </form>
    </main>
</div>

<script>
    function toggleModulePermissions(moduleSlug) {
        const checkboxes = document.querySelectorAll('.perm-checkbox-' + moduleSlug);
        if (checkboxes.length === 0) return;
        
        // If at least one is unchecked, check all. Otherwise, uncheck all.
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => cb.checked = anyUnchecked);
    }
</script>
@endsection
