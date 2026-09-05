<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Client\PortalController;
use App\Models\AuditLog;
use App\Models\CardSubscription;
use App\Models\Parcel;
use App\Models\Payment;
use App\Models\TravelRoute;
use App\Models\TripConsumption;
use App\Models\VipCard;
use App\Models\VipClient;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $isStaff = in_array($user->role?->slug, ['admin', 'agent'], true);

        if (! $isStaff) {
            return app(PortalController::class)();
        }

        $now = Carbon::now();
        $today = Carbon::today();

        // 1. KPI Metrics
        $totalVipClients = VipClient::where('status', 'active')->count();
        $activeCardsCount = VipCard::where('status', 'active')->count();
        $consumedTripsCount = TripConsumption::count();
        $todayTripsCount = TripConsumption::whereDate('travel_date', $today)->count();
        $registeredParcelsCount = Parcel::where('status', 'registered')->count();
        $totalParcelsCount = Parcel::count();
        $pendingPaymentsCount = Payment::where('payment_status', 'pending')->count();
        $suspendedCardsCount = VipCard::where('status', 'suspended')->count();
        
        $expiredSubscriptionsCount = CardSubscription::query()
            ->where(function ($query) use ($now) {
                $query->where('status', 'expired')
                    ->orWhere(fn ($q) => $q->where('expires_at', '<', $now)->whereIn('status', ['active', 'pending_payment']));
            })
            ->count();

        $kpis = [
            'vip_clients' => $totalVipClients,
            'active_cards' => $activeCardsCount,
            'suspended_cards' => $suspendedCardsCount,
            'expired_subscriptions' => $expiredSubscriptionsCount,
            'pending_payments' => $pendingPaymentsCount,
            'consumed_trips' => $consumedTripsCount,
            'trips_today' => $todayTripsCount,
            'registered_parcels' => $registeredParcelsCount,
            'total_parcels' => $totalParcelsCount,
        ];

        // 2. Weekly Activity (7 days: Monday to Sunday)
        $startOfWeek = $now->copy()->startOfWeek();
        $weeklyDays = [];
        $dayLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
        $maxDayVolume = 1;

        for ($i = 0; $i < 7; $i++) {
            $currentDay = $startOfWeek->copy()->addDays($i);
            $dayTrips = TripConsumption::whereDate('travel_date', $currentDay)->count();
            $dayParcels = Parcel::whereDate('registered_at', $currentDay)->count();
            $totalVolume = $dayTrips + $dayParcels;

            if ($totalVolume > $maxDayVolume) {
                $maxDayVolume = $totalVolume;
            }

            $weeklyDays[] = [
                'label' => $dayLabels[$i],
                'date' => $currentDay->format('d/m'),
                'isToday' => $currentDay->isToday(),
                'isPastOrToday' => $currentDay->lessThanOrEqualTo($now),
                'trips' => $dayTrips,
                'parcels' => $dayParcels,
                'total' => $totalVolume,
            ];
        }

        // Add percentage height for CSS bar rendering
        foreach ($weeklyDays as &$day) {
            $pct = $maxDayVolume > 0 ? round(($day['total'] / $maxDayVolume) * 85) + 15 : 20;
            $day['bar_height_pct'] = min(100, max(18, $pct));
        }
        unset($day);

        // 3. Featured VIP Route / Next Departure
        $featuredRoute = TravelRoute::where('status', 'active')->first();

        // 4. Recent Trips (5 latest)
        $recentTrips = TripConsumption::query()
            ->with(['client', 'travelRoute', 'departureAgency'])
            ->latest('consumed_at')
            ->limit(5)
            ->get();

        // 5. Recent Parcels (4 latest)
        $recentParcels = Parcel::query()
            ->with(['sender', 'originAgency', 'destinationAgency'])
            ->latest('id')
            ->limit(4)
            ->get();

        // 6. Subscription Utilization Rate (Gauge Data)
        $activeSubs = CardSubscription::where('status', 'active')->get();
        $totalAllocatedTrips = $activeSubs->sum('trips_total');
        $remainingTrips = $activeSubs->sum('trips_remaining');
        $consumedFromActive = max(0, $totalAllocatedTrips - $remainingTrips);
        $utilizationRate = $totalAllocatedTrips > 0 ? round(($consumedFromActive / $totalAllocatedTrips) * 100) : 45;

        $subscriptionStats = [
            'total_trips' => $totalAllocatedTrips,
            'consumed_trips' => $consumedFromActive,
            'remaining_trips' => $remainingTrips,
            'utilization_rate' => $utilizationRate,
            'active_subs_count' => $activeSubs->count(),
            'expired_subs_count' => $expiredSubscriptionsCount,
        ];

        // 7. Team & Desk Activity (Today's Operations)
        $todayTripsOps = TripConsumption::whereDate('created_at', $today)->count();
        $todayParcelsOps = Parcel::whereDate('created_at', $today)->count();
        $todayPaymentsOps = Payment::whereDate('created_at', $today)->count();
        $auditLogsToday = AuditLog::whereDate('created_at', $today)->count();
        $totalDailyOps = max($auditLogsToday, ($todayTripsOps + $todayParcelsOps + $todayPaymentsOps));

        $deskActivity = [
            'total_ops' => $totalDailyOps,
            'today_trips' => $todayTripsOps,
            'today_parcels' => $todayParcelsOps,
            'today_payments' => $todayPaymentsOps,
            'user_agency' => $user->agency?->name ?? 'Agence Centrale Buca',
        ];

        $recentPendingPayments = Payment::query()
            ->with(['client', 'subscription.card'])
            ->where('payment_status', 'pending')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('backoffice.dashboard', [
            'isStaff' => true,
            'user' => $user,
            'kpis' => $kpis,
            'weeklyDays' => $weeklyDays,
            'featuredRoute' => $featuredRoute,
            'recentTrips' => $recentTrips,
            'recentParcels' => $recentParcels,
            'subscriptionStats' => $subscriptionStats,
            'deskActivity' => $deskActivity,
            'recentPendingPayments' => $recentPendingPayments,
        ]);
    }
}
