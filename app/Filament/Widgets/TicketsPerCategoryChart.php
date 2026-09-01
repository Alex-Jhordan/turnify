<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;

class TicketsPerCategoryChart extends ChartWidget
{
    protected ?string $heading = 'Tickets Per Category Chart';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $categories = Category::withCount('tickets')->get();
        $labels = $categories->pluck('name')->toArray();
        $data = $categories->pluck('tickets_count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tickets',
                    'data' => $data,
                    'backgroundColor' => [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#ec4899',
                        '#06b6d4',
                        '#84cc16',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
