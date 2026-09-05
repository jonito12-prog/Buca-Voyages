<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->string('sender_email')->nullable()->after('sender_phone');
        });
    }

    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropColumn('sender_email');
        });
    }
};
