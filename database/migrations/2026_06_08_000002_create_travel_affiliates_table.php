<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vip_client_id')->constrained('vip_clients')->noActionOnDelete();
            $table->foreignId('vip_card_id')->nullable()->constrained('vip_cards')->noActionOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('relationship')->nullable();
            $table->string('identity_type')->nullable();
            $table->string('identity_number')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->timestamps();

            $table->index(['vip_client_id', 'status']);
        });

        Schema::table('trip_consumptions', function (Blueprint $table) {
            $table->foreignId('travel_affiliate_id')->nullable()->after('vip_client_id')->constrained('travel_affiliates')->noActionOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trip_consumptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('travel_affiliate_id');
        });

        Schema::dropIfExists('travel_affiliates');
    }
};
