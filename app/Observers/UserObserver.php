<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserGrowth;

class UserObserver
{
    public function created(User $user): void
    {
        if (($user->role ?? null) !== 'user') {
            return;
        }

        $growthDate = now()->toDateString();
        $groupId = $user->group_id ?: 'default';

        UserGrowth::firstOrCreate(
            ['user_id' => $user->id],
            [
                'group_id'    => $groupId,
                'growth_date' => $growthDate,
            ]
        );
    }
}
