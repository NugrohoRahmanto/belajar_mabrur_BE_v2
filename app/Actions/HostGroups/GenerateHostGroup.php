<?php

namespace App\Actions\HostGroups;

use App\Models\Content;
use App\Models\HostGroup;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateHostGroup
{
    public function handle(array $payload): array
    {
        return DB::transaction(function () use ($payload) {
            $groupId = $this->generateGroupId($payload['name']);
            $templateGroupId = $payload['template_group_id']
                ?? HostGroup::default()?->group_id
                ?? 'default';

            /** @var HostGroup $hostGroup */
            $hostGroup = HostGroup::create([
                'group_id'    => $groupId,
                'name'        => $payload['name'],
                'host'        => $this->generateHostValue($groupId),
                'is_active'   => Arr::get($payload, 'is_active', true),
                'is_default'  => false,
                'description' => Arr::get($payload, 'description'),
                'metadata'    => [
                    'aliases' => [],
                ],
            ]);

            $copiedContents = Arr::get($payload, 'copy_contents', true)
                ? $this->duplicateContents($templateGroupId, $hostGroup->group_id)
                : 0;

            $hostsCreated = $this->createAccounts(
                $hostGroup,
                'host',
                (int) Arr::get($payload, 'host_count', 1),
                Arr::get($payload, 'host_password')
            );

            $usersCreated = $this->createAccounts(
                $hostGroup,
                'user',
                (int) Arr::get($payload, 'user_count', 1),
                Arr::get($payload, 'user_password')
            );

            return [$hostGroup, $copiedContents, $hostsCreated, $usersCreated];
        });
    }

    private function duplicateContents(?string $fromGroupId, string $toGroupId): int
    {
        if (!$fromGroupId || $fromGroupId === $toGroupId) {
            return 0;
        }

        if (Content::forGroup($toGroupId)->exists()) {
            return 0;
        }

        $sourceContents = Content::forGroup($fromGroupId)->get();

        foreach ($sourceContents as $content) {
            $clone = $content->replicate();
            $clone->group_id = $toGroupId;
            $clone->save();
        }

        return $sourceContents->count();
    }

    private function createAccounts(HostGroup $group, string $role, int $count, ?string $password): int
    {
        if ($count <= 0 || empty($password)) {
            return 0;
        }

        $created = 0;
        $sequence = 1;

        while ($created < $count) {
            $username = $this->formatUsername($group->group_id, $role, $sequence);

            if (User::forGroup($group->group_id)->where('username', $username)->exists()) {
                $sequence++;
                continue;
            }

            User::create([
                'username' => $username,
                'name'     => $this->formatDisplayName($group->name, $role, $sequence),
                'password' => Hash::make($password),
                'role'     => $role,
                'group_id' => $group->group_id,
                'email'    => null,
            ]);

            $created++;
            $sequence++;
        }

        return $created;
    }

    private function generateGroupId(string $name): string
    {
        $slug = Str::slug($name);

        if (empty($slug)) {
            $slug = 'group';
        }

        $original = $slug;
        $counter = 1;

        while (HostGroup::where('group_id', $slug)->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function generateHostValue(string $groupId): string
    {
        return $groupId;
    }

    private function formatUsername(string $groupId, string $role, int $sequence): string
    {
        $prefix = Str::slug($role, '-');
        $suffix = str_pad($sequence, 2, '0', STR_PAD_LEFT);

        return "{$prefix}-{$groupId}-{$suffix}";
    }

    private function formatDisplayName(?string $groupName, string $role, int $sequence): string
    {
        $label = ucfirst($role);
        $number = str_pad($sequence, 2, '0', STR_PAD_LEFT);

        if ($groupName) {
            return "{$label} {$groupName} #{$number}";
        }

        return "{$label} #{$number}";
    }
}
