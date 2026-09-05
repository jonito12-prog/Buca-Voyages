<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ParcelDelivered;
use Tests\TestCase;

class LogisticsTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;
    private VipClient $vipClient;
    private VipCard $vipCard;
    private CardSubscription $subscription;
    private TravelRoute $route;
    private Agency $originAgency;
    private Agency $destinationAgency;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed Roles and Permissions (same as BucaVoyagesDemoSeeder)
        $roleData = [
            'name' => 'Agent agence',
            'slug' => 'agent',
            'description' => 'Gère les clients, cartes, forfaits, voyages et paiements en agence.',
        ];
        $role = Role::create($roleData);

        // 2. Seed Agencies
        $this->originAgency = Agency::create([
            'name' => 'Agence Mvan',
            'city' => 'Yaoundé',
            'district' => 'Mvan',
            'address' => 'Mvan, Yaoundé',
            'phone' => '+237 690 000 001',
            'status' => 'active',
        ]);

        $this->destinationAgency = Agency::create([
            'name' => 'Agence Mboppi',
            'city' => 'Douala',
            'district' => 'Mboppi',
            'address' => 'Mboppi, Douala',
            'phone' => '+237 690 000 002',
            'status' => 'active',
        ]);

        // 3. Seed Routes
        $this->route = TravelRoute::create([
            'departure_city' => 'Yaoundé',
            'arrival_city' => 'Douala',
            'distance_km' => 245,
            'estimated_duration' => '4h30',
            'status' => 'active',
        ]);

        // 4. Seed User
        $this->agent = User::create([
            'name' => 'Agent Test',
            'email' => 'agent@bucavoyages.test',
            'phone' => '+237 699 100 000',
            'role_id' => $role->id,
            'agency_id' => $this->originAgency->id,
            'password' => bcrypt('Password@123'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // 5. Seed VIP Package
        $package = Package::create([
            'name' => 'Mensuel VIP',
            'period_type' => 'monthly',
            'trip_count' => 10,
            'validity_days' => 30,
            'price' => 90000,
            'travel_route_id' => $this->route->id,
            'class_type' => 'vip',
            'status' => 'active',
        ]);

        // 6. Create VIP Client
        $this->vipClient = VipClient::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'phone' => '+237 655 444 333',
            'email' => 'jean.dupont@gmail.com',
            'status' => 'active',
            'created_by' => $this->agent->id,
        ]);

        // 7. Create VIP Card
        $this->vipCard = VipCard::create([
            'vip_client_id' => $this->vipClient->id,
            'card_number' => 'VIP-123456',
            'status' => 'active',
            'created_by' => $this->agent->id,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        // 8. Create Subscription and confirm payment to make card active
        $this->subscription = CardSubscription::create([
            'vip_card_id' => $this->vipCard->id,
            'package_id' => $package->id,
            'starts_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays(30),
            'trips_total' => 10,
            'trips_remaining' => 10,
            'unit_price' => 9000,
            'total_amount' => 90000,
            'status' => 'active',
            'created_by' => $this->agent->id,
        ]);

        Payment::create([
            'card_subscription_id' => $this->subscription->id,
            'vip_client_id' => $this->vipClient->id,
            'amount' => 90000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'paid_at' => Carbon::now(),
            'received_by' => $this->agent->id,
        ]);
    }

    public function test_can_book_trip(): void
    {
        $response = $this->actingAs($this->agent)
            ->post(route('trips.store'), [
                'vip_card_id' => $this->vipCard->id,
                'travel_route_id' => $this->route->id,
                'departure_agency_id' => $this->originAgency->id,
                'travel_date' => now()->format('Y-m-d'),
                'notes' => 'Voyage test sans bagages',
            ]);

        $response->assertRedirect(route('vip-cards.show', $this->vipCard));
        $this->subscription->refresh();
        $this->assertEquals(9, $this->subscription->trips_remaining);

        $this->assertDatabaseHas('trip_consumptions', [
            'vip_card_id' => $this->vipCard->id,
            'vip_client_id' => $this->vipClient->id,
            'travel_route_id' => $this->route->id,
            'departure_agency_id' => $this->originAgency->id,
        ]);
    }

    public function test_can_create_parcel_with_cash(): void
    {
        $response = $this->actingAs($this->agent)
            ->post(route('parcels.store'), [
                'sender_name' => 'Mireille Ken',
                'sender_phone' => '677777777',
                'sender_email' => 'mireille.ken@gmail.com',
                'receiver_name' => 'Paul Paul',
                'receiver_phone' => '699999999',
                'origin_agency_id' => $this->originAgency->id,
                'destination_agency_id' => $this->destinationAgency->id,
                'size_category' => 'medium',
                'declared_value' => 50000,
                'shipping_price' => 4000,
                'payment_method' => 'cash',
                'notes' => 'Vêtements et chaussures',
            ]);

        $parcel = Parcel::first();
        $this->assertNotNull($parcel);
        $response->assertRedirect(route('parcels.show', $parcel));

        $this->assertDatabaseHas('parcels', [
            'sender_name' => 'Mireille Ken',
            'sender_phone' => '677777777',
            'sender_email' => 'mireille.ken@gmail.com',
            'receiver_name' => 'Paul Paul',
            'origin_agency_id' => $this->originAgency->id,
            'destination_agency_id' => $this->destinationAgency->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'registered',
            'shipping_price' => 4000,
        ]);
        $this->assertStringStartsWith('CLS-', $parcel->tracking_code);
    }

    public function test_can_deliver_parcel_with_cni_and_send_email(): void
    {
        Mail::fake();

        $parcel = Parcel::create([
            'sender_name' => 'Jean Dupont',
            'sender_phone' => '655555555',
            'sender_email' => 'jean.dupont@gmail.com',
            'receiver_name' => 'Paul Dupont',
            'receiver_phone' => '677777777',
            'origin_agency_id' => $this->originAgency->id,
            'destination_agency_id' => $this->destinationAgency->id,
            'tracking_code' => 'CLS-TEST123',
            'size_category' => 'small',
            'shipping_price' => 2000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'registered',
            'created_by' => $this->agent->id,
            'registered_at' => now(),
        ]);

        // Deliver directly to receiver with CNI
        $response = $this->actingAs($this->agent)
            ->patch(route('parcels.deliver', $parcel), [
                'receiver_identity_number' => 'CNI 102938475',
            ]);
        $response->assertRedirect(route('parcels.show', $parcel));
        $parcel->refresh();
        $this->assertEquals('delivered', $parcel->status);
        $this->assertEquals('CNI 102938475', $parcel->receiver_identity_number);
        $this->assertNotNull($parcel->delivered_at);

        // Assert that the email was sent to the sender
        Mail::assertSent(ParcelDelivered::class, function ($mail) use ($parcel) {
            return $mail->hasTo('jean.dupont@gmail.com') && $mail->parcel->id === $parcel->id;
        });
    }
}
