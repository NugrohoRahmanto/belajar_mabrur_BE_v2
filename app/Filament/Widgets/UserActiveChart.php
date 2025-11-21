<?php

namespace App\Filament\Widgets;

use App\Models\UserDailyActivity;
use Filament\Widgets\ChartWidget;

class UserActiveChart extends ChartWidget
{
    protected static ?string $heading = 'User Active (Dynamic)';

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

        $raw = UserDailyActivity::selectRaw('activity_date, COUNT(user_id) as total')
            ->whereBetween('activity_date', [$start, $end])
            ->groupBy('activity_date')
            ->orderBy('activity_date')
            ->get()
            ->keyBy('activity_date');

        // generate tanggal lengkap
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
                    'label' => 'Active Users',
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
