<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_routes', function (Blueprint $table) {
            $table->id();
            $table->string('departure_city');
            $table->string('arrival_city');
            $table->unsignedInteger('distance_km')->nullable();
            $table->string('estimated_duration')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['departure_city', 'arrival_city', 'status']);
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('period_type');
            $table->unsignedInteger('trip_count');
            $table->unsignedInteger('validity_days');
            $table->decimal('price', 12, 2);
            $table->foreignId('travel_route_id')->nullable()->constrained('travel_routes')->nullOnDelete();
            $table->string('class_type')->default('vip');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['period_type', 'class_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
        Schema::dropIfExists('travel_routes');
    }
};
