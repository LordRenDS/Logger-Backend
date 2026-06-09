<?php

namespace App\Policies;

use App\Models\Pc;
use App\Models\User;

class PcPolicy
{
    /**
     * Determine if the user can view, update, or delete the PC.
     */
    public function view(User $user, Pc $pc): bool
    {
        return $user->id === $pc->user_id;
    }

    public function update(User $user, Pc $pc): bool
    {
        return $user->id === $pc->user_id;
    }

    public function delete(User $user, Pc $pc): bool
    {
        return $user->id === $pc->user_id;
    }
}
