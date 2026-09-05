<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
        ]);

        $request->user()->update([
            'password' => $request->password,
        ]);

        return back()->with('status', 'Votre mot de passe a été modifié avec succès.');
    }
}
