<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_subscription_id')->constrained('card_subscriptions')->cascadeOnDelete();
            $table->foreignId('vip_client_id')->constrained('vip_clients')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->string('payment_status')->default('pending');
            $table->string('transaction_reference')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['payment_status', 'payment_method']);
        });

        Schema::create('trip_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_subscription_id')->constrained('card_subscriptions')->cascadeOnDelete();
            $table->foreignId('vip_card_id')->constrained('vip_cards')->cascadeOnDelete();
            $table->foreignId('vip_client_id')->constrained('vip_clients')->cascadeOnDelete();
            $table->foreignId('travel_route_id')->constrained('travel_routes')->restrictOnDelete();
            $table->foreignId('departure_agency_id')->constrained('agencies')->restrictOnDelete();
            $table->foreignId('arrival_agency_id')->nullable()->constrained('agencies')->nullOnDelete();
            $table->date('travel_date');
            $table->timestamp('consumed_at');
            $table->foreignId('consumed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('trips_debited')->default(1);
            $table->string('reference')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vip_card_id', 'travel_date']);
            $table->index(['card_subscription_id', 'consumed_at']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vip_client_id')->nullable()->constrained('vip_clients')->nullOnDelete();
            $table->string('type');
            $table->string('channel');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('module');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['module', 'action']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('trip_consumptions');
        Schema::dropIfExists('payments');
    }
};
