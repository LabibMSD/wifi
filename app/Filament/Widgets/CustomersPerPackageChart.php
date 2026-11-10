<?php

namespace App\Filament\Widgets;

use App\Models\Package;
use Filament\Widgets\ChartWidget;

class CustomersPerPackageChart extends ChartWidget
{
    protected ?string $heading = 'Pelanggan per Paket';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $packages = Package::query()
            ->withCount('customers')
            ->orderBy('speed_mbps')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('general.dashboard.charts.customers_label'),
                    'data' => $packages->pluck('customers_count')->toArray(),
                    'backgroundColor' => [
                        '#3B82F6',
                        '#10B981',
                        '#F59E0B',
                        '#EF4444',
                        '#8B5CF6',
                        '#EC4899',
                    ],
                ],
            ],
            'labels' => $packages->pluck('display_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}