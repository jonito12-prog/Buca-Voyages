<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_clients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('identity_type')->nullable();
            $table->string('identity_number')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['phone', 'status']);
            $table->index(['last_name', 'first_name']);
        });

        Schema::create('vip_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_client_id')->constrained('vip_clients')->cascadeOnDelete();
            $table->string('card_number')->unique();
            $table->string('qr_code')->nullable();
            $table->string('card_type')->default('vip');
            $table->string('status')->default('active');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vip_client_id', 'status']);
        });

        Schema::create('card_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_card_id')->constrained('vip_cards')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->restrictOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->unsignedInteger('trips_total');
            $table->unsignedInteger('trips_remaining');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->foreignId('renewed_from_id')->nullable()->constrained('card_subscriptions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vip_card_id', 'status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_subscriptions');
        Schema::dropIfExists('vip_cards');
        Schema::dropIfExists('vip_clients');
    }
};
