<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Village;
use App\Models\Hamlet;
use App\Models\Package;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        return [
            Stat::make(__('general.dashboard.stats.total_customers'), Customer::query()->count())
                ->description(__('general.dashboard.stats.customers_description'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make(__('general.dashboard.stats.total_villages'), Village::query()->count())
                ->description(__('general.dashboard.stats.villages_description'))
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),
            Stat::make(__('general.dashboard.stats.total_hamlets'), Hamlet::query()->count())
                ->description(__('general.dashboard.stats.hamlets_description'))
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('warning'),
            Stat::make(__('general.dashboard.stats.total_packages'), Package::query()->count())
                ->description(__('general.dashboard.stats.packages_description'))
                ->descriptionIcon('heroicon-m-wifi')
                ->color('primary'),
        ];
    }
}