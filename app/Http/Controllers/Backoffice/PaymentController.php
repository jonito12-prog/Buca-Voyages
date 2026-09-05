<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\VipCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private VipCardService $cards)
    {
    }

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'pending');
        $search = trim((string) $request->query('search', ''));

        $payments = Payment::query()
            ->with(['client', 'subscription.package', 'subscription.card', 'receiver'])
            ->when(in_array($status, ['pending', 'paid'], true), fn ($query) => $query->where('payment_status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('transaction_reference', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($q) => $q
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"))
                        ->orWhereHas('subscription.card', fn ($q) => $q->where('card_number', 'like', "%{$search}%"));
                });
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = Payment::where('payment_status', 'pending')->count();

        return view('backoffice.payments.index', compact('payments', 'status', 'search', 'pendingCount'));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['client', 'subscription.package', 'subscription.card', 'receiver']);

        return view('backoffice.payments.show', compact('payment'));
    }

    public function confirm(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'transaction_reference' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('payments', 'transaction_reference')->ignore($payment->id),
            ],
        ]);

        $reference = trim((string) ($data['transaction_reference'] ?? ''));

        $this->cards->confirmPayment(
            $payment,
            $request->user(),
            $reference !== '' ? $reference : null
        );

        return redirect()->route('payments.show', $payment)
            ->with('status', 'Paiement confirme. Le forfait est maintenant actif.');
    }
}
