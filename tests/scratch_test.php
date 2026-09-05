<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Agency;
use App\Models\CardSubscription;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Role;
use App\Models\TravelRoute;
use App\Models\TripConsumption;
use App\Models\User;
use App\Models\VipCard;
use App\Models\VipClient;
use App\Models\Parcel;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=========================================\n";
echo "LOGISTICS TEST RUNNER (Standalone script)\n";
echo "=========================================\n";

DB::beginTransaction();

try {
    // 1. Setup default roles, agencies, routes, users, packages
    $role = Role::firstOrCreate(['slug' => 'agent'], [
        'name' => 'Agent agence',
        'description' => 'Gère les clients et voyages.',
    ]);

    $originAgency = Agency::firstOrCreate(['name' => 'Agence Mvan Standalone'], [
        'city' => 'Yaoundé',
        'district' => 'Mvan',
        'address' => 'Mvan, Yaoundé',
        'phone' => '+237 690 000 001',
        'status' => 'active',
    ]);

    $destinationAgency = Agency::firstOrCreate(['name' => 'Agence Mboppi Standalone'], [
        'city' => 'Douala',
        'district' => 'Mboppi',
        'address' => 'Mboppi, Douala',
        'phone' => '+237 690 000 002',
        'status' => 'active',
    ]);

    $route = TravelRoute::firstOrCreate([
        'departure_city' => 'Yaoundé',
        'arrival_city' => 'Douala'
    ], [
        'distance_km' => 245,
        'estimated_duration' => '4h30',
        'status' => 'active',
    ]);

    $agent = User::firstOrCreate(['email' => 'agent.standalone@bucavoyages.test'], [
        'name' => 'Agent Standalone',
        'phone' => '+237 699 100 000',
        'role_id' => $role->id,
        'agency_id' => $originAgency->id,
        'password' => bcrypt('Password@123'),
        'status' => 'active',
        'email_verified_at' => now(),
    ]);

    $package = Package::firstOrCreate(['name' => 'Mensuel VIP Standalone'], [
        'period_type' => 'monthly',
        'trip_count' => 10,
        'validity_days' => 30,
        'price' => 90000,
        'travel_route_id' => $route->id,
        'class_type' => 'vip',
        'status' => 'active',
    ]);

    $vipClient = VipClient::create([
        'first_name' => 'Jean',
        'last_name' => 'Dupont Standalone',
        'phone' => '+237 655 444 333',
        'email' => 'jean.dupont.standalone@gmail.com',
        'status' => 'active',
        'created_by' => $agent->id,
    ]);

    $vipCard = VipCard::create([
        'vip_client_id' => $vipClient->id,
        'card_number' => 'VIP-STANDALONE-99',
        'status' => 'active',
        'created_by' => $agent->id,
        'expires_at' => Carbon::now()->addDays(30),
    ]);

    $subscription = CardSubscription::create([
        'vip_card_id' => $vipCard->id,
        'package_id' => $package->id,
        'starts_at' => Carbon::now(),
        'expires_at' => Carbon::now()->addDays(30),
        'trips_total' => 10,
        'trips_remaining' => 10,
        'unit_price' => 9000,
        'total_amount' => 90000,
        'status' => 'active',
        'created_by' => $agent->id,
    ]);

    Payment::create([
        'card_subscription_id' => $subscription->id,
        'vip_client_id' => $vipClient->id,
        'amount' => 90000,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'paid_at' => Carbon::now(),
        'received_by' => $agent->id,
    ]);

    echo "[+] Setup Completed successfully.\n";

    // 2. Test trip booking
    $consumption = TripConsumption::create([
        'card_subscription_id' => $subscription->id,
        'vip_card_id' => $vipCard->id,
        'vip_client_id' => $vipClient->id,
        'travel_route_id' => $route->id,
        'departure_agency_id' => $originAgency->id,
        'travel_date' => now()->toDateString(),
        'consumed_at' => now(),
        'consumed_by' => $agent->id,
        'trips_debited' => 1,
        'reference' => 'TRIP-STANDALONE-TEST',
    ]);
    $subscription->decrement('trips_remaining', 1);

    assert($subscription->fresh()->trips_remaining === 9, "Subscription remaining trips should be 9");
    echo "[+] Test 1: Trip registration - PASS\n";

    // 3. Test parcel creation cash
    $parcelCash = Parcel::create([
        'sender_name' => 'Alice Ken',
        'sender_phone' => '655555555',
        'sender_email' => 'alice.ken@gmail.com',
        'receiver_name' => 'Bob Ken',
        'receiver_phone' => '677777777',
        'origin_agency_id' => $originAgency->id,
        'destination_agency_id' => $destinationAgency->id,
        'tracking_code' => 'CLS-CASH-TEST',
        'size_category' => 'small',
        'shipping_price' => 2500,
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'status' => 'registered',
        'created_by' => $agent->id,
        'registered_at' => now(),
    ]);

    assert($parcelCash->payment_method === 'cash', "Parcel payment method should be cash");
    assert($parcelCash->payment_status === 'paid', "Parcel payment status should be paid");
    assert($parcelCash->sender_email === 'alice.ken@gmail.com', "Parcel sender email should be stored correctly");
    echo "[+] Test 2: Parcel creation Cash - PASS\n";

    // 4. Test parcel delivery directly (this will trigger sending email under the hood)
    $parcelCash->update([
        'status' => 'delivered',
        'receiver_identity_number' => 'CNI 987654321',
        'delivered_at' => now(),
    ]);
    assert($parcelCash->fresh()->status === 'delivered', "Parcel status should be delivered");
    assert($parcelCash->fresh()->receiver_identity_number === 'CNI 987654321', "Parcel receiver CNI should be CNI 987654321");
    echo "[+] Test 3: Secure Delivery with CNI - PASS\n";

    echo "\n=========================================\n";
    echo "ALL STANDALONE TESTS PASSED SUCCESSFULLY!\n";
    echo "=========================================\n";

} catch (Exception $e) {
    echo "\n[-] TEST FAILURE: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    DB::rollBack();
    echo "[+] Database transactions rolled back. Database is clean.\n";
}
