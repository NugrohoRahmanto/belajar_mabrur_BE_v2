<?php

namespace App\Filament\Pages;

use App\Models\HostGroup;
use Filament\Pages\Page;

class GroupInfo extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    protected static ?string $navigationLabel = 'Info Group';
    protected static ?string $title = 'Informasi Host Group';
    protected static string $view = 'filament.pages.group-info';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    public function getHostGroupsProperty()
    {
        return HostGroup::query()
            ->withCount([
                'users as host_accounts_count' => fn ($query) => $query->where('role', 'host'),
                'users as user_accounts_count' => fn ($query) => $query->where('role', 'user'),
            ])
            ->orderByDesc('created_at')
            ->get();
    }
}
