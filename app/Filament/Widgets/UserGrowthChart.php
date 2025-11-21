<?php

namespace App\Filament\Widgets;

use App\Models\User;
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

        $start = now()->subDays($days - 1)->toDateString();
        $end   = now()->toDateString();

        $raw = User::selectRaw('DATE(created_at) as day, COUNT(id) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // generate full date list
        $dates = collect();
        $cursor = now()->subDays($days - 1);
        for ($i = 0; $i < $days; $i++) {
            $dates->push($cursor->toDateString());
            $cursor->addDay();
        }

        $totals = $dates->map(fn($day) => $raw[$day]->total ?? 0);

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data'  => $totals->toArray(),
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
