<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VipClient;
use App\Services\ClientAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VipClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');

        if ($status === 'pending') {
            $users = \App\Models\User::query()
                ->whereHas('role', fn ($query) => $query->where('slug', 'client'))
                ->whereDoesntHave('vipClient')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                })
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString();

            $clients = $users->through(function ($user) {
                $client = new VipClient();
                $client->id = null;
                $parts = explode(' ', $user->name, 2);
                $client->first_name = $parts[0] ?? '';
                $client->last_name = $parts[1] ?? '';
                $client->email = $user->email;
                $client->phone = $user->phone;
                $client->status = 'pending';
                $client->cards_count = 0;
                $client->user_id = $user->id;
                return $client;
            });
        } else {
            $clients = VipClient::query()
                ->withCount('cards')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('identity_number', 'like', "%{$search}%");
                    });
                })
                ->when(in_array($status, ['active', 'suspended', 'archived'], true), fn ($query) => $query->where('status', $status))
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString();
        }

        return view('backoffice.clients.index', compact('clients', 'search', 'status'));
    }

    public function create(Request $request): View
    {
        return view('backoffice.clients.create', [
            'prefillEmail' => $request->query('email'),
            'prefillFirstName' => $request->query('first_name'),
            'prefillLastName' => $request->query('last_name'),
            'prefillPhone' => $request->query('phone'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $userId = null;
        if (!empty($data['email'])) {
            $existingUser = \App\Models\User::where('email', $data['email'])
                ->whereHas('role', fn ($query) => $query->where('slug', 'client'))
                ->first();
            
            if ($existingUser) {
                $userId = $existingUser->id;
            } elseif ($request->boolean('create_user_account')) {
                $clientRole = \App\Models\Role::where('slug', 'client')->firstOrFail();
                $user = \App\Models\User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password' => $data['password'],
                    'role_id' => $clientRole->id,
                    'status' => 'active',
                ]);

                event(new \Illuminate\Auth\Events\Registered($user));
                $userId = $user->id;

                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(
                        new \App\Mail\ClientAccountCreated($user, $data['password'])
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Échec d'envoi du mail de bienvenue : " . $e->getMessage());
                }
            }
        }

        $client = VipClient::create($data + [
            'created_by' => $request->user()->id,
            'user_id' => $userId,
        ]);

        $this->audit($request, 'vip_client.created', $client, null, $client->only([
            'first_name', 'last_name', 'phone', 'email', 'status',
        ]));

        return redirect()->route('vip-clients.show', $client)
            ->with('status', $userId ? 'Client VIP cree et compte en ligne relie avec succes.' : 'Client VIP cree avec succes.');
    }

    public function show(VipClient $vipClient): View
    {
        $vipClient->load([
            'cards.subscriptions.package',
            'creator',
            'travelAffiliates.card',
            'tripConsumptions.travelRoute',
            'parcels.originAgency',
            'parcels.destinationAgency',
        ]);

        $receivedParcels = \App\Models\Parcel::where('receiver_phone', $vipClient->phone)
            ->with(['originAgency', 'destinationAgency'])
            ->latest('id')
            ->get();

        return view('backoffice.clients.show', compact('vipClient', 'receivedParcels'));
    }

    public function edit(VipClient $vipClient): View
    {
        return view('backoffice.clients.edit', compact('vipClient'));
    }

    public function update(Request $request, VipClient $vipClient): RedirectResponse
    {
        $data = $this->validatedData($request, $vipClient);

        $oldValues = $vipClient->only([
            'first_name', 'last_name', 'phone', 'email', 'address', 'identity_type', 'identity_number', 'status',
        ]);

        $userId = $vipClient->user_id;

        if (!$userId && !empty($data['email'])) {
            $existingUser = \App\Models\User::where('email', $data['email'])
                ->whereHas('role', fn ($query) => $query->where('slug', 'client'))
                ->first();
            
            if ($existingUser) {
                $userId = $existingUser->id;
            } elseif ($request->boolean('create_user_account')) {
                $clientRole = \App\Models\Role::where('slug', 'client')->firstOrFail();
                $user = \App\Models\User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password' => $data['password'],
                    'role_id' => $clientRole->id,
                    'status' => 'active',
                ]);

                event(new \Illuminate\Auth\Events\Registered($user));
                $userId = $user->id;

                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(
                        new \App\Mail\ClientAccountCreated($user, $data['password'])
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Échec d'envoi du mail de bienvenue : " . $e->getMessage());
                }
            }
        } elseif ($userId && !empty($data['password'])) {
            $user = \App\Models\User::findOrFail($userId);
            $user->update([
                'password' => $data['password'],
            ]);
        }

        $vipClient->update($data + ['user_id' => $userId]);

        $this->audit($request, 'vip_client.updated', $vipClient, $oldValues, $vipClient->fresh()->only(array_keys($oldValues)));

        return redirect()->route('vip-clients.show', $vipClient)
            ->with('status', 'Informations du client mises a jour.');
    }

    public function archive(Request $request, VipClient $vipClient): RedirectResponse
    {
        $oldStatus = $vipClient->status;
        $vipClient->update(['status' => 'archived']);

        $this->audit($request, 'vip_client.archived', $vipClient, ['status' => $oldStatus], ['status' => 'archived']);

        return redirect()->route('vip-clients.index')
            ->with('status', 'Client archive. Son historique reste conserve.');
    }

    public function destroy(Request $request, VipClient $vipClient): RedirectResponse
    {
        $hasOperations = $vipClient->cards()->whereHas('subscriptions')->exists()
            || $vipClient->parcels()->exists();

        if ($hasOperations) {
            throw ValidationException::withMessages([
                'delete' => 'Ce client possede deja un forfait, un historique de voyages ou de colis. Archivez-le afin de conserver la tracabilite.',
            ]);
        }

        DB::transaction(function () use ($request, $vipClient): void {
            $values = $vipClient->only(['first_name', 'last_name', 'phone', 'email']);

            $this->audit($request, 'vip_client.deleted', $vipClient, $values, ['deleted' => true]);

            $vipClient->cards()->delete();
            $vipClient->delete();
        });

        return redirect()->route('vip-clients.index')
            ->with('status', 'Client supprime definitivement.');
    }

    private function validatedData(Request $request, ?VipClient $client = null): array
    {
        if ($request->input('identity_type') === 'Autre') {
            $request->merge([
                'identity_type' => $request->input('identity_type_other') ?: 'Récépissé'
            ]);
        }

        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['F', 'M', 'Autre'])],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'identity_type' => ['nullable', 'string', 'max:100'],
            'identity_number' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'suspended', 'archived'])],
            'create_user_account' => ['nullable', 'boolean'],
            'password' => ['nullable', 'required_if:create_user_account,1', 'string', 'min:8'],
        ]);
    }

    private function audit(Request $request, string $action, VipClient $client, ?array $oldValues, array $newValues): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'module' => 'vip_clients',
            'entity_type' => VipClient::class,
            'entity_id' => $client->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }
}
