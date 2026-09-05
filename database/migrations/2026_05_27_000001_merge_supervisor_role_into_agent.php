<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $agentId = DB::table('roles')->where('slug', 'agent')->value('id');
        $supervisorId = DB::table('roles')->where('slug', 'superviseur')->value('id');

        if (! $agentId || ! $supervisorId) {
            return;
        }

        DB::transaction(function () use ($agentId, $supervisorId): void {
            DB::table('users')
                ->where('role_id', $supervisorId)
                ->update([
                    'role_id' => $agentId,
                    'updated_at' => now(),
                ]);

            DB::table('users')
                ->where('email', 'superviseur@bucavoyages.test')
                ->where('name', 'Superviseur Mvan')
                ->update([
                    'name' => 'Agent Mvan',
                    'updated_at' => now(),
                ]);

            DB::table('permission_role')->where('role_id', $supervisorId)->delete();
            DB::table('roles')->where('id', $supervisorId)->delete();
        });
    }

    public function down(): void
    {
        // La fusion conserve volontairement les anciens comptes en tant qu'agents.
    }
};
