<?php

namespace App\Http\Middleware;

use App\Models\HostGroup;
use Closure;
use Illuminate\Http\Request;

class ResolveHostGroup
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $hostGroup = $this->resolveGroup($request);

        if (!$hostGroup) {
            return response()->json([
                'code'    => 404,
                'status'  => 'Not Found',
                'message' => 'Host is not registered for any group',
            ], 404);
        }

        $request->attributes->set('host_group', $hostGroup);
        $request->attributes->set('group_id', $hostGroup->group_id);

        return $next($request);
    }

    private function resolveGroup(Request $request): ?HostGroup
    {
        $hostCandidates = array_filter([
            $request->header('X-Tenant-Host'),
            $request->header('X-Forwarded-Host'),
            $request->header('X-Original-Host'),
            $request->getHost(),
        ]);

        foreach ($hostCandidates as $host) {
            $group = HostGroup::resolveByHost(strtolower($host));
            if ($group) {
                return $group;
            }
        }

        $groupIdHeader = $request->header('X-Group-ID');
        if ($groupIdHeader) {
            $group = HostGroup::resolveByGroup($groupIdHeader);
            if ($group) {
                return $group;
            }
        }

        $groupIdInput = $request->input('group_id');
        if ($groupIdInput) {
            $group = HostGroup::resolveByGroup($groupIdInput);
            if ($group) {
                return $group;
            }
        }

        if ($this->shouldFallbackToDefault($request)) {
            return HostGroup::default();
        }

        return null;
    }

    private function shouldFallbackToDefault(Request $request): bool
    {
        $host = strtolower($request->getHost());
        $localHosts = ['localhost', '127.0.0.1', ''];

        return in_array($host, $localHosts, true) || app()->environment('local');
    }
}
