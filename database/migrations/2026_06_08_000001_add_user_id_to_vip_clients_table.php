<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('vip_clients', 'user_id')) {
            return;
        }

        Schema::table('vip_clients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('created_by')->constrained('users')->noActionOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('vip_clients', 'user_id')) {
            return;
        }

        Schema::table('vip_clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
