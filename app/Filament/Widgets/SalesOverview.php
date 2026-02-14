<?php

namespace App\Filament\Widgets;

use App\Models\Medicine;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $todayTotal = Transaction::query()
            ->whereDate('transaction_date', now()->toDateString())
            ->sum('total_amount');

        $totalStock = Medicine::query()->sum('stock');

        $expiringSoonCount = Medicine::query()
            ->whereBetween('expiry_date', [
                now()->toDateString(),
                now()->addDays(30)->toDateString(),
            ])
            ->count();

        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format((float) $todayTotal, 0, ',', '.')),
            Stat::make('Total Stok Obat', (string) $totalStock),
            Stat::make('Akan Kadaluarsa (< 30 hari)', (string) $expiringSoonCount),
        ];
    }
}

