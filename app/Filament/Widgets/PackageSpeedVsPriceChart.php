<?php

namespace App\Filament\Widgets;

use App\Models\Package;
use Filament\Widgets\ChartWidget;
use Filament\Support\RawJs;

class PackageSpeedVsPriceChart extends ChartWidget
{
    protected ?string $heading = 'Paket: Kecepatan vs Harga';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $packages = Package::query()->orderBy('speed_mbps')->get();

        $data = $packages->map(fn ($package) => [
            'x' => $package->speed_mbps,
            'y' => $package->price,
            'label' => $package->name,
        ])->toArray();

        return [
            'datasets' => [
                [
                    'label' => __('general.dashboard.charts.price_label'),
                    'data' => $data,
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#3B82F6',
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8,
                    'showLine' => true,
                    'tension' => 0.1,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'scatter';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
            {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Kecepatan (Mbps)',
                        },
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Harga (Rp)',
                        },
                        ticks: {
                            callback: (value) => 'Rp ' + value.toLocaleString('id-ID'),
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const label = context.raw.label || '';
                                return label + ': ' + context.raw.x + ' Mbps, Rp ' + context.raw.y.toLocaleString('id-ID');
                            },
                        },
                    },
                },
            }
        JS);
    }
}
