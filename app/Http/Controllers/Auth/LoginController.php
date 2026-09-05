<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->audit(null, 'login.failed', $request, ['email' => $request->email]);

            return back()
                ->withErrors(['email' => 'Email ou mot de passe incorrect.'])
                ->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            $this->audit($user->id, 'login.blocked', $request, ['status' => $user->status]);

            return back()
                ->withErrors(['email' => 'Ce compte est suspendu ou desactive.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();
        $this->audit($user->id, 'login.success', $request);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->audit($userId, 'logout', $request);

        return redirect()->route('login')->with('status', 'Vous etes deconnecte.');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));
        $this->audit(null, 'password_reset.requested', $request, ['email' => $request->email]);

        return back()->with('status', 'Si un compte correspond a cette adresse, un lien de reinitialisation vient d etre envoye.');
    }

    private function audit(?int $userId, string $action, Request $request, array $values = []): void
    {
        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => 'auth',
            'entity_type' => 'users',
            'entity_id' => $userId,
            'new_values' => $values ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }
}
