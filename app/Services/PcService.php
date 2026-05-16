<?php

namespace App\Services;

use App\Models\Pc;
use App\Models\User;
use Carbon\Carbon;

class PcService
{
    /**
     * Find a PC by its unique ID or create a new one for the given user.
     *
     * @param User $user
     * @param string $uniqueId
     * @param string|null $name
     * @return Pc
     */
    public function findOrCreatePc(User $user, string $uniqueId, ?string $name = null): Pc
    {
        $pc = Pc::where('unique_id', $uniqueId)->first();

        if ($pc) {
            if ($pc->user_id !== $user->id) {
                throw new \Illuminate\Auth\Access\AuthorizationException('This device is registered to another user.');
            }

            $pc->update([
                'last_seen_at' => Carbon::now(),
                'name' => $name ?? $pc->name,
            ]);
        } else {
            $pc = Pc::create([
                'user_id' => $user->id,
                'unique_id' => $uniqueId,
                'name' => $name,
                'last_seen_at' => Carbon::now(),
            ]);
        }

        return $pc;
    }
}
