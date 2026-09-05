<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CardSubscription;
use App\Models\Package;
use App\Models\Payment;
use App\Models\TravelAffiliate;
use App\Models\TravelRoute;
use App\Models\TripConsumption;
use App\Models\User;
use App\Models\VipCard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VipCardService
{
    public function subscribe(
        VipCard $card,
        Package $package,
        User $actor,
        string $paymentMethod,
        string $paymentStatus = 'paid',
        ?string $transactionReference = null,
        ?CardSubscription $renewedFrom = null
    ): CardSubscription {
        if ($card->status === 'suspended') {
            throw ValidationException::withMessages([
                'card' => 'Impossible de renouveler une carte suspendue.',
            ]);
        }

        return DB::transaction(function () use ($card, $package, $actor, $paymentMethod, $paymentStatus, $transactionReference, $renewedFrom) {
            if ($renewedFrom) {
                $renewedFrom->update(['status' => 'renewed']);
            }

            $startsAt = Carbon::now();
            $subscription = CardSubscription::create([
                'vip_card_id' => $card->id,
                'package_id' => $package->id,
                'starts_at' => $startsAt,
                'expires_at' => $startsAt->copy()->addDays((int) $package->validity_days),
                'trips_total' => (int) $package->trip_count,
                'trips_remaining' => (int) $package->trip_count,
                'unit_price' => round((float) $package->price / max(1, (int) $package->trip_count), 2),
                'total_amount' => $package->price,
                'status' => $paymentStatus === 'paid' ? 'active' : 'pending_payment',
                'renewed_from_id' => $renewedFrom?->id,
                'created_by' => $actor->id,
            ]);

            Payment::create([
                'card_subscription_id' => $subscription->id,
                'vip_client_id' => $card->vip_client_id,
                'amount' => $package->price,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'transaction_reference' => $transactionReference,
                'paid_at' => $paymentStatus === 'paid' ? Carbon::now() : null,
                'received_by' => $actor->id,
            ]);

            $card->update([
                'status' => 'active',
                'expires_at' => $subscription->expires_at,
            ]);

            $this->audit($actor, 'subscription.created', 'card_subscriptions', $subscription->id, [
                'card_number' => $card->card_number,
                'package' => $package->name,
                'payment_status' => $paymentStatus,
            ]);

            return $subscription;
        });
    }

    public function consumeTrip(
        CardSubscription $subscription,
        TravelRoute $route,
        int $departureAgencyId,
        User $actor,
        ?int $arrivalAgencyId = null,
        ?Carbon $travelDate = null,
        string $notes = '',
        ?TravelAffiliate $travelAffiliate = null
    ): TripConsumption {
        $this->ensureCanConsume($subscription);

        return DB::transaction(function () use ($subscription, $route, $departureAgencyId, $actor, $arrivalAgencyId, $travelDate, $notes, $travelAffiliate) {
            $subscription->refresh();

            if ($subscription->trips_remaining < 1) {
                throw ValidationException::withMessages([
                    'trips_remaining' => 'Solde insuffisant.',
                ]);
            }

            $card = $subscription->card()->with('client')->firstOrFail();

            if ($travelAffiliate) {
                if ($travelAffiliate->vip_client_id !== $card->vip_client_id) {
                    throw ValidationException::withMessages([
                        'travel_affiliate_id' => 'Cette personne affiliee n appartient pas au titulaire de la carte.',
                    ]);
                }

                if ($travelAffiliate->status !== 'active') {
                    throw ValidationException::withMessages([
                        'travel_affiliate_id' => 'Cette personne affiliee n est pas active.',
                    ]);
                }

                if ($travelAffiliate->vip_card_id && $travelAffiliate->vip_card_id !== $card->id) {
                    throw ValidationException::withMessages([
                        'travel_affiliate_id' => 'Cette personne affiliee n est pas autorisee sur cette carte.',
                    ]);
                }
            }

            $reference = 'TRIP-' . now()->format('YmdHis') . '-' . $card->id . '-' . random_int(100, 999);

            $consumption = TripConsumption::create([
                'card_subscription_id' => $subscription->id,
                'vip_card_id' => $card->id,
                'vip_client_id' => $card->vip_client_id,
                'travel_affiliate_id' => $travelAffiliate?->id,
                'travel_route_id' => $route->id,
                'departure_agency_id' => $departureAgencyId,
                'arrival_agency_id' => $arrivalAgencyId,
                'travel_date' => ($travelDate ?? Carbon::now())->toDateString(),
                'consumed_at' => Carbon::now(),
                'consumed_by' => $actor->id,
                'trips_debited' => 1,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            $subscription->decrement('trips_remaining');

            if ($subscription->fresh()->trips_remaining === 0) {
                $subscription->update(['status' => 'completed']);
            }

            $this->audit($actor, 'trip.consumed', 'trip_consumptions', $consumption->id, [
                'reference' => $reference,
                'card_number' => $card->card_number,
                'route' => $route->departure_city . ' - ' . $route->arrival_city,
                'traveler' => $travelAffiliate?->fullName() ?? $card->client->first_name . ' ' . $card->client->last_name,
            ]);

            return $consumption;
        });
    }

    public function suspendCard(VipCard $card, User $actor, string $reason): void
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'La raison de suspension est obligatoire.',
            ]);
        }

        $oldStatus = $card->status;

        $card->update([
            'status' => 'suspended',
            'suspended_at' => Carbon::now(),
            'suspension_reason' => $reason,
        ]);

        $this->audit($actor, 'card.suspended', 'vip_cards', $card->id, [
            'old_status' => $oldStatus,
            'new_status' => 'suspended',
            'reason' => $reason,
        ]);
    }

    public function confirmPayment(Payment $payment, User $actor, ?string $transactionReference = null): Payment
    {
        if ($payment->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'payment' => 'Ce paiement est deja confirme.',
            ]);
        }

        return DB::transaction(function () use ($payment, $actor, $transactionReference) {
            $payment->loadMissing(['subscription.card', 'subscription.package']);

            $reference = trim((string) ($transactionReference ?? $payment->transaction_reference ?? ''));
            if ($reference === '') {
                $reference = 'PAY-' . now()->format('YmdHis') . '-' . random_int(100, 999);
            }

            if (Payment::query()
                ->where('transaction_reference', $reference)
                ->where('id', '!=', $payment->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'transaction_reference' => 'Cette reference de paiement est deja utilisee.',
                ]);
            }

            $payment->update([
                'payment_status' => 'paid',
                'transaction_reference' => $reference,
                'paid_at' => Carbon::now(),
                'received_by' => $actor->id,
            ]);

            $subscription = $payment->subscription;
            if ($subscription && $subscription->status === 'pending_payment') {
                $subscription->update(['status' => 'active']);
            }

            $card = $subscription?->card;
            if ($card && $card->status !== 'suspended') {
                $card->update([
                    'status' => 'active',
                    'expires_at' => $subscription->expires_at,
                ]);
            }

            $this->audit($actor, 'payment.confirmed', 'payments', $payment->id, [
                'transaction_reference' => $reference,
                'card_number' => $card?->card_number,
                'amount' => (float) $payment->amount,
            ]);

            return $payment->fresh();
        });
    }

    public function reactivateCard(VipCard $card, User $actor): void
    {
        $card->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        $this->audit($actor, 'card.reactivated', 'vip_cards', $card->id, [
            'card_number' => $card->card_number,
        ]);
    }

    private function ensureCanConsume(CardSubscription $subscription): void
    {
        $subscription->loadMissing('card');

        if ($subscription->card->status === 'suspended') {
            throw ValidationException::withMessages([
                'card' => 'Carte suspendue : consommation impossible.',
            ]);
        }

        if ($subscription->status !== 'active') {
            throw ValidationException::withMessages([
                'subscription' => 'Le forfait n est pas actif.',
            ]);
        }

        if ($subscription->expires_at->isPast()) {
            $subscription->update(['status' => 'expired']);

            throw ValidationException::withMessages([
                'subscription' => 'Forfait expire : consommation impossible.',
            ]);
        }

        $paid = Payment::query()
            ->where('card_subscription_id', $subscription->id)
            ->where('payment_status', 'paid')
            ->exists();

        if (! $paid) {
            throw ValidationException::withMessages([
                'payment' => 'Paiement non confirme.',
            ]);
        }
    }

    private function audit(User $actor, string $action, string $module, int $entityId, array $values): void
    {
        AuditLog::create([
            'user_id' => $actor->id,
            'action' => $action,
            'module' => $module,
            'entity_type' => $module,
            'entity_id' => $entityId,
            'new_values' => $values,
        ]);
    }
}
