<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TravelAffiliate;
use App\Services\ClientAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AffiliateController extends Controller
{
    public function __construct(private ClientAccountService $accounts)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $vipClient = $this->accounts->resolveVipClient($request->user());

        if (! $vipClient) {
            throw ValidationException::withMessages([
                'affiliate' => 'Votre fiche client VIP n est pas encore active.',
            ]);
        }

        TravelAffiliate::create(
            $this->validatedData($request, $vipClient) + [
                'vip_client_id' => $vipClient->id,
                'created_by' => $request->user()->id,
            ]
        );

        return back()->with('status', 'Personne autorisee a voyager ajoutee avec succes.');
    }


    private function validatedData(Request $request, $vipClient): array
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'relationship' => ['nullable', Rule::in(['Conjoint', 'Enfant', 'Parent', 'Employe', 'Assistant', 'Autre'])],
            'identity_type' => ['nullable', 'string', 'max:100'],
            'identity_number' => ['nullable', 'string', 'max:100'],
            'vip_card_id' => ['nullable', 'integer', Rule::exists('vip_cards', 'id')->where('vip_client_id', $vipClient->id)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $data['status'] = 'active';

        return $data;
    }
}
