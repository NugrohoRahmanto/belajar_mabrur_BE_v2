<?php

namespace App\Filament\Widgets;

use App\Models\UserGrowth;
use Filament\Widgets\ChartWidget;

class UserGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'User Growth (Dynamic)';

    protected function getFilters(): array
    {
        return [
            '7'   => 'Last 7 Days',
            '30'  => 'Last 30 Days',
            '365' => 'Last 365 Days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 7);

        $now = now();
        $rangeStart = $now->copy()->subDays($days - 1)->startOfDay();
        $rangeEnd   = $now->copy()->endOfDay();

        $groupId = optional(auth()->user())->group_id;

        $raw = UserGrowth::query()
            ->selectRaw('growth_date, COUNT(id) as total_new_users')
            ->whereBetween('growth_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->when($groupId, fn ($query) => $query->where('group_id', $groupId))
            ->groupBy('growth_date')
            ->orderBy('growth_date')
            ->get()
            ->keyBy(fn ($row) => $row->growth_date->toDateString());

        // generate full date list
        $dates = collect();
        $cursor = $rangeStart->copy();
        for ($i = 0; $i < $days; $i++) {
            $dates->push($cursor->toDateString());
            $cursor->addDay();
        }

        $dailyTotals = $dates->map(fn ($day) => (int) ($raw[$day]->total_new_users ?? 0))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data'  => $dailyTotals,
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
