<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\VipClient;
use App\Models\VipCard;
use App\Models\CardSubscription;
use App\Models\Agency;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ParcelController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');
        $origin = $request->query('origin');
        $destination = $request->query('destination');

        $parcels = Parcel::query()
            ->with(['sender', 'originAgency', 'destinationAgency', 'creator'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('tracking_code', 'like', "%{$search}%")
                      ->orWhere('sender_name', 'like', "%{$search}%")
                      ->orWhere('sender_phone', 'like', "%{$search}%")
                      ->orWhere('receiver_name', 'like', "%{$search}%")
                      ->orWhere('receiver_phone', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['registered', 'delivered'], true), fn ($q) => $q->where('status', $status))
            ->when($origin, fn ($q) => $q->where('origin_agency_id', $origin))
            ->when($destination, fn ($q) => $q->where('destination_agency_id', $destination))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $agencies = Agency::where('status', 'active')->orderBy('city')->orderBy('name')->get();

        return view('backoffice.parcels.index', compact('parcels', 'search', 'status', 'origin', 'destination', 'agencies'));
    }

    public function create(Request $request): View
    {
        $vipClientId = $request->query('vip_client_id');
        $vipClient = $vipClientId ? VipClient::findOrFail($vipClientId) : null;

        $clients = VipClient::where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $agencies = Agency::where('status', 'active')->orderBy('city')->orderBy('name')->get();

        return view('backoffice.parcels.create', compact('vipClient', 'clients', 'agencies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sender_vip_client_id' => ['nullable', 'integer', 'exists:vip_clients,id'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_phone' => ['required', 'string', 'max:50'],
            'sender_email' => ['nullable', 'email', 'max:255'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:50'],
            'origin_agency_id' => ['required', 'integer', 'exists:agencies,id'],
            'destination_agency_id' => ['required', 'integer', 'exists:agencies,id', 'different:origin_agency_id'],
            'size_category' => ['required', Rule::in(['document', 'small', 'medium', 'large', 'special'])],
            'declared_value' => ['nullable', 'numeric', 'min:0'],
            'shipping_price' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(['cash', 'momo', 'orange_money'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($request, $data) {
            $paymentStatus = 'paid';

            $trackingCode = 'CLS-' . strtoupper(bin2hex(random_bytes(3)));
            while (Parcel::where('tracking_code', $trackingCode)->exists()) {
                $trackingCode = 'CLS-' . strtoupper(bin2hex(random_bytes(3)));
            }

            $parcel = Parcel::create([
                'sender_vip_client_id' => $data['sender_vip_client_id'] ?? null,
                'sender_name' => $data['sender_name'],
                'sender_phone' => $data['sender_phone'],
                'sender_email' => $data['sender_email'] ?? null,
                'receiver_name' => $data['receiver_name'],
                'receiver_phone' => $data['receiver_phone'],
                'origin_agency_id' => $data['origin_agency_id'],
                'destination_agency_id' => $data['destination_agency_id'],
                'tracking_code' => $trackingCode,
                'size_category' => $data['size_category'],
                'declared_value' => $data['declared_value'] ?? 0,
                'shipping_price' => $data['shipping_price'],
                'payment_method' => $data['payment_method'],
                'payment_status' => $paymentStatus,
                'status' => 'registered',
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
                'registered_at' => Carbon::now(),
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'parcel.registered',
                'module' => 'parcels',
                'entity_type' => Parcel::class,
                'entity_id' => $parcel->id,
                'new_values' => $parcel->only([
                    'tracking_code', 'sender_name', 'receiver_name', 'shipping_price', 'payment_method', 'status'
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);

            return redirect()->route('parcels.show', $parcel)
                ->with('status', "Colis {$trackingCode} enregistré avec succès.");
        });
    }

    public function show(Parcel $parcel): View
    {
        $parcel->load(['sender', 'originAgency', 'destinationAgency', 'creator']);
        return view('backoffice.parcels.show', compact('parcel'));
    }

    public function deliver(Request $request, Parcel $parcel): RedirectResponse
    {
        $data = $request->validate([
            'receiver_identity_number' => ['required', 'string', 'max:100'],
        ]);

        $oldStatus = $parcel->status;
        $parcel->update([
            'status' => 'delivered',
            'receiver_identity_number' => $data['receiver_identity_number'],
            'delivered_at' => Carbon::now(),
        ]);

        $email = $parcel->sender_email ?: ($parcel->sender?->email ?: null);
        if ($email) {
            try {
                \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\ParcelDelivered($parcel));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Échec d'envoi du mail de notification de colis : " . $e->getMessage());
            }
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'parcel.delivered',
            'module' => 'parcels',
            'entity_type' => Parcel::class,
            'entity_id' => $parcel->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => 'delivered',
                'receiver_identity_number' => $data['receiver_identity_number'],
                'delivered_at' => Carbon::now(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return redirect()->route('parcels.show', $parcel)
            ->with('status', 'Colis livré avec succès et CNI enregistrée.');
    }
}
