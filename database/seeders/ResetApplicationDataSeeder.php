<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\CardSubscription;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Role;
use App\Models\TravelAffiliate;
use App\Models\TripConsumption;
use App\Models\User;
use App\Models\VipCard;
use App\Models\VipClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetApplicationDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            TripConsumption::query()->delete();
            if (Schema::hasTable('travel_affiliates')) {
                TravelAffiliate::query()->delete();
            }
            Payment::query()->delete();
            CardSubscription::query()->delete();
            VipCard::query()->delete();
            Notification::query()->delete();
            VipClient::query()->delete();
            AuditLog::query()->delete();

            $adminRoleId = Role::where('slug', 'admin')->value('id');

            User::query()
                ->when($adminRoleId, fn ($query) => $query->where('role_id', '!=', $adminRoleId))
                ->delete();
        });
    }
}
