<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Services\ClientAccountService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $clientRole = Role::where('slug', 'client')->firstOrFail();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'role_id' => $clientRole->id,
            'status' => 'active',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'register.success',
            'module' => 'auth',
            'entity_type' => 'users',
            'entity_id' => $user->id,
            'new_values' => ['email' => $user->email, 'role' => 'client'],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        app(ClientAccountService::class)->resolveVipClient($user);

        Auth::login($user);
        $request->session()->regenerate();
        event(new Registered($user));

        return redirect()->route('verification.notice')
            ->with('status', 'Compte cree. Consultez votre boite email pour activer votre acces.');
    }
}
