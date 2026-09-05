<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_vip_client_id')->nullable()->constrained('vip_clients')->onDelete('set null');
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_identity_number')->nullable();
            $table->foreignId('origin_agency_id')->constrained('agencies');
            $table->foreignId('destination_agency_id')->constrained('agencies');
            $table->string('tracking_code')->unique();
            $table->string('size_category')->default('medium'); // document, small, medium, large, special
            $table->decimal('declared_value', 12, 2)->default(0);
            $table->decimal('shipping_price', 12, 2)->default(0);
            $table->string('payment_method')->default('cash'); // cash, momo, orange_money, vip_card_debit
            $table->string('payment_status')->default('pending'); // pending, paid, refunded
            $table->string('status')->default('registered'); // registered, dispatched, arrived, delivered, returned, lost
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcels');
    }
};
