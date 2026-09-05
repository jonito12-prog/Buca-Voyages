<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Agency;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(Request $request): View
    {
        $roles = Role::with('permissions')->get();
        
        $permissionsGrouped = Permission::all()->groupBy('module');
        
        // Paginate users to handle larger volumes
        $users = User::with(['role', 'agency'])
            ->whereHas('role', function ($query) {
                $query->whereIn('slug', ['admin', 'agent']);
            })
            ->orWhereNull('role_id')
            ->orderBy('name')
            ->paginate(15, ['*'], 'users_page');

        $allRoles = Role::all();
        $agencies = Agency::where('status', 'active')->orderBy('city')->get();

        return view('backoffice.security.index', compact('roles', 'permissionsGrouped', 'users', 'allRoles', 'agencies'));
    }

    public function createRole(): View
    {
        $permissionsGrouped = Permission::all()->groupBy('module');
        return view('backoffice.security.roles.create', compact('permissionsGrouped'));
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $slug = Str::slug($data['name']);
        
        // Guard against duplicate slug
        if (Role::where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'Un rôle similaire avec le même identifiant existe déjà.'])->withInput();
        }

        $role = DB::transaction(function () use ($request, $data, $slug) {
            $role = Role::create([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
            ]);

            if (!empty($data['permissions'])) {
                $role->permissions()->sync($data['permissions']);
            }

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'role.created',
                'module' => 'Securite',
                'entity_type' => Role::class,
                'entity_id' => $role->id,
                'new_values' => [
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'permissions' => $data['permissions'] ?? [],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);

            return $role;
        });

        return redirect()->route('security.index')
            ->with('status', "Le rôle '{$role->name}' a été créé avec succès.");
    }

    public function editRole(Role $role): View
    {
        // Guard against editing default client/admin roles directly to prevent system breakage
        if (in_array($role->slug, ['admin', 'client'], true)) {
            abort(403, "Le rôle par défaut '{$role->name}' ne peut pas être modifié.");
        }

        $permissionsGrouped = Permission::all()->groupBy('module');
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('backoffice.security.roles.edit', compact('role', 'permissionsGrouped', 'rolePermissionIds'));
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        if (in_array($role->slug, ['admin', 'client'], true)) {
            abort(403, "Le rôle par défaut '{$role->name}' ne peut pas être modifié.");
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        DB::transaction(function () use ($request, $role, $data) {
            $oldPermissions = $role->permissions->pluck('id')->toArray();
            
            $role->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            $role->permissions()->sync($data['permissions'] ?? []);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'role.updated',
                'module' => 'Securite',
                'entity_type' => Role::class,
                'entity_id' => $role->id,
                'old_values' => [
                    'name' => $role->getOriginal('name'),
                    'description' => $role->getOriginal('description'),
                    'permissions' => $oldPermissions,
                ],
                'new_values' => [
                    'name' => $role->name,
                    'description' => $role->description,
                    'permissions' => $data['permissions'] ?? [],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
        });

        return redirect()->route('security.index')
            ->with('status', "Le rôle '{$role->name}' a été mis à jour.");
    }

    public function destroyRole(Request $request, Role $role): RedirectResponse
    {
        if (in_array($role->slug, ['admin', 'agent', 'client'], true)) {
            return back()->withErrors(['role' => "Le rôle système '{$role->name}' ne peut pas être supprimé."]);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => "Le rôle '{$role->name}' ne peut pas être supprimé car il est attribué à des utilisateurs."]);
        }

        DB::transaction(function () use ($request, $role) {
            $oldValues = $role->toArray();
            $oldPermissions = $role->permissions->pluck('id')->toArray();
            $oldValues['permissions'] = $oldPermissions;

            $role->permissions()->detach();
            $role->delete();

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'role.deleted',
                'module' => 'Securite',
                'entity_type' => Role::class,
                'entity_id' => $role->id,
                'old_values' => $oldValues,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
        });

        return redirect()->route('security.index')
            ->with('status', "Le rôle a été supprimé.");
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'agency_id' => ['nullable', 'integer', 'exists:agencies,id'],
        ]);

        // Prevent self-demotion from admin
        if ($user->id === $request->user()->id && $user->role?->slug === 'admin' && (!isset($data['role_id']) || Role::find($data['role_id'])?->slug !== 'admin')) {
            return back()->withErrors(['user' => "Vous ne pouvez pas modifier votre propre rôle d'administrateur."]);
        }

        DB::transaction(function () use ($request, $user, $data) {
            $oldRoleId = $user->role_id;
            $oldAgencyId = $user->agency_id;

            $user->update([
                'role_id' => $data['role_id'],
                'agency_id' => $data['agency_id'],
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'user.role_updated',
                'module' => 'Securite',
                'entity_type' => User::class,
                'entity_id' => $user->id,
                'old_values' => [
                    'role_id' => $oldRoleId,
                    'agency_id' => $oldAgencyId,
                ],
                'new_values' => [
                    'role_id' => $user->role_id,
                    'agency_id' => $user->agency_id,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
        });

        return redirect()->route('security.index')
            ->with('status', "Les privilèges de l'utilisateur '{$user->name}' ont été mis à jour.");
    }
}
