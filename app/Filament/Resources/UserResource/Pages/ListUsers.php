<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\HostGroup;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('bulk-generate')
                ->label('Generate Multiple Users')
                ->icon('heroicon-o-users')
                ->color('primary')
                ->modalHeading('Generate Users')
                ->modalWidth('md')
                ->form([
                    Forms\Components\Select::make('group_id')
                        ->label('Target Group')
                        ->options(fn () => HostGroup::orderBy('name')->pluck('name', 'group_id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('role')
                        ->label('Role')
                        ->options([
                            'host' => 'Host',
                            'user' => 'User',
                        ])
                        ->default('user')
                        ->required(),
                    Forms\Components\TextInput::make('count')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(200)
                        ->default(5)
                        ->label('Number of Accounts')
                        ->required(),
                    Forms\Components\TextInput::make('username_prefix')
                        ->label('Username Prefix')
                        ->default('member')
                        ->maxLength(50)
                        ->required(),
                    Forms\Components\TextInput::make('name_prefix')
                        ->label('Display Name Prefix')
                        ->default('Member')
                        ->maxLength(50),
                    Forms\Components\TextInput::make('password')
                        ->label('Password for All')
                        ->password()
                        ->revealable()
                        ->minLength(6)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $created = $this->generateUsers($data);

                    Notification::make()
                        ->title('Users generated')
                        ->body("{$created} user(s) created for group {$data['group_id']}.")
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function generateUsers(array $data): int
    {
        $count    = (int) $data['count'];
        $groupId  = $data['group_id'];
        $role     = $data['role'];
        $password = Hash::make($data['password']);
        $prefix   = $this->normalizeUsernamePrefix($data['username_prefix'] ?? null);
        $namePref = $this->resolveNamePrefix($data, $role);
        $nextNameNumber = $this->nextDisplayNameNumber($groupId, $role, $namePref);
        $nextUsernameNumber = $this->nextUsernameNumber($groupId, $prefix);

        $created = 0;
        $numberOffset = 0;

        while ($created < $count) {
            $username = $this->formatUsername($prefix, $groupId, $nextUsernameNumber + $numberOffset);

            if (User::forGroup($groupId)->where('username', $username)->exists()) {
                $numberOffset++;
                continue;
            }

            User::create([
                'username' => $username,
                'name'     => $this->formatDisplayName($namePref, $nextNameNumber + $created),
                'password' => $password,
                'role'     => $role,
                'group_id' => $groupId,
            ]);

            $created++;
            $numberOffset++;
        }

        return $created;
    }

    protected function formatUsername(string $prefix, string $groupId, int $sequence): string
    {
        $cleanGroup = $this->sanitizeGroupForUsername($groupId);
        $prefix = $prefix ?: 'user';
        $suffix = str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);

        return "{$prefix}-{$cleanGroup}-{$suffix}";
    }

    protected function formatDisplayName(string $prefix, int $sequence): string
    {
        $number = str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
        $prefix = trim($prefix) ?: 'Member';

        return "{$prefix} {$number}";
    }

    protected function resolveNamePrefix(array $data, string $role): string
    {
        $prefix = trim($data['name_prefix'] ?? '');

        if ($prefix !== '') {
            return $prefix;
        }

        $usernamePrefix = trim($data['username_prefix'] ?? '');

        if ($usernamePrefix !== '') {
            return $usernamePrefix;
        }

        return ucfirst($role);
    }

    protected function nextDisplayNameNumber(string $groupId, string $role, string $prefix): int
    {
        $query = User::forGroup($groupId)
            ->where('role', $role)
            ->select('name');

        if ($prefix !== '') {
            $query->where('name', 'like', $prefix . '%');
        }

        $max = $query->get()
            ->map(fn ($user) => $this->extractNumericSuffix($user->name))
            ->filter()
            ->max();

        return ($max ?? 0) + 1;
    }

    protected function extractNumericSuffix(?string $value): ?int
    {
        if (!$value) {
            return null;
        }

        if (preg_match('/(\d+)\s*$/', $value, $matches)) {
            $number = (int) ltrim($matches[1], '0');
            return $number > 0 ? $number : 0;
        }

        return null;
    }

    protected function normalizeUsernamePrefix(?string $prefix): string
    {
        $prefix = trim($prefix ?? '');

        if ($prefix === '') {
            return 'user';
        }

        $slug = Str::slug($prefix);

        return $slug !== '' ? $slug : 'user';
    }

    protected function sanitizeGroupForUsername(string $groupId): string
    {
        $slug = Str::slug($groupId);

        return $slug !== '' ? $slug : Str::of($groupId)->slug('-');
    }

    protected function nextUsernameNumber(string $groupId, string $prefix): int
    {
        $groupSlug = $this->sanitizeGroupForUsername($groupId);
        $pattern = "{$prefix}-{$groupSlug}-";

        $max = User::forGroup($groupId)
            ->where('username', 'like', $pattern . '%')
            ->pluck('username')
            ->map(fn ($username) => $this->extractNumericSuffix($username))
            ->filter()
            ->max();

        return ($max ?? 0) + 1;
    }
}
