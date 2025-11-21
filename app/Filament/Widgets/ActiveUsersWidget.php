<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActiveUsersWidget extends BaseWidget
{
    protected ?string $heading = 'User Activity Overview';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Total registered users')
                ->color('primary'),

            Stat::make(
                'Active (last 10 mins)',
                User::where('last_active_at', '>=', now()->subMinutes(10))->count()
            )
                ->description('Users considered online')
                ->color('success'),

            Stat::make(
                'Active Today',
                User::whereDate('last_active_at', today())->count()
            )
                ->description('Logged in or active today')
                ->color('info'),
        ];
    }
}
