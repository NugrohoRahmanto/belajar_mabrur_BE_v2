<?php

namespace App\Http\Controllers;

use App\Models\HostGroup;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function requestGroupId(Request $request): ?string
    {
        return $request->attributes->get('group_id');
    }

    protected function requestHostGroup(Request $request): ?HostGroup
    {
        return $request->attributes->get('host_group');
    }

    protected function alignRequestGroupWithUser(Request $request, User $user): void
    {
        $request->attributes->set('group_id', $user->group_id);

        if ($user->relationLoaded('hostGroup')) {
            $request->attributes->set('host_group', $user->hostGroup);
        } else {
            $request->attributes->set('host_group', $user->hostGroup()->first());
        }
    }
}
