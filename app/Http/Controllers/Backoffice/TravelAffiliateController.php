<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TravelAffiliate;
use App\Models\VipClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TravelAffiliateController extends Controller
{
    public function store(Request $request, VipClient $vipClient): RedirectResponse
    {
        $affiliate = TravelAffiliate::create(
            $this->validatedData($request, $vipClient) + [
                'vip_client_id' => $vipClient->id,
                'created_by' => $request->user()->id,
            ]
        );

        $this->audit($request, 'travel_affiliate.created', $vipClient, $affiliate);

        return back()->with('status', 'Personne affiliee ajoutee avec succes.');
    }

    public function toggleStatus(Request $request, VipClient $vipClient, TravelAffiliate $affiliate): RedirectResponse
    {
        $this->ensureBelongsToClient($vipClient, $affiliate);

        $affiliate->update([
            'status' => $affiliate->status === 'active' ? 'inactive' : 'active',
        ]);

        $this->audit($request, 'travel_affiliate.status_changed', $vipClient, $affiliate);

        return back()->with('status', $affiliate->status === 'active' ? 'Affilie reactive.' : 'Affilie desactive.');
    }

    private function validatedData(Request $request, VipClient $vipClient): array
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

    private function ensureBelongsToClient(VipClient $vipClient, TravelAffiliate $affiliate): void
    {
        if ($affiliate->vip_client_id !== $vipClient->id) {
            throw ValidationException::withMessages([
                'affiliate' => 'Affiliation introuvable pour ce client.',
            ]);
        }
    }

    private function audit(Request $request, string $action, VipClient $client, TravelAffiliate $affiliate): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'module' => 'travel_affiliates',
            'entity_type' => TravelAffiliate::class,
            'entity_id' => $affiliate->id,
            'new_values' => [
                'client_id' => $client->id,
                'name' => $affiliate->fullName(),
                'status' => $affiliate->status,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }
}
