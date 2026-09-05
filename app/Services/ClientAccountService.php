<?php

namespace App\Services;

use App\Models\User;
use App\Models\VipClient;

class ClientAccountService
{
    public function resolveVipClient(User $user): ?VipClient
    {
        if ($user->role?->slug !== 'client') {
            return null;
        }

        $linked = VipClient::where('user_id', $user->id)->first();
        if ($linked) {
            return $linked;
        }

        if (! $user->email) {
            return null;
        }

        $candidate = VipClient::query()
            ->whereNull('user_id')
            ->where('email', $user->email)
            ->first();

        if ($candidate) {
            $candidate->update(['user_id' => $user->id]);

            return $candidate->fresh();
        }

        return null;
    }

    public function linkByEmail(VipClient $vipClient): void
    {
        if ($vipClient->user_id || ! $vipClient->email) {
            return;
        }

        $user = User::query()
            ->where('email', $vipClient->email)
            ->whereHas('role', fn ($query) => $query->where('slug', 'client'))
            ->first();

        if ($user) {
            VipClient::where('user_id', $user->id)->update(['user_id' => null]);
            $vipClient->update(['user_id' => $user->id]);
        }
    }
}
