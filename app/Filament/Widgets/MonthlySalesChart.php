<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlySalesChart extends ChartWidget
{
    protected static ?string $heading = 'Penjualan Bulanan';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $start = now()->subMonths(11)->startOfMonth();
        $end = now()->endOfMonth();

        $rows = Transaction::query()
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as ym, sum(total_amount) as total")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $labels = [];
        $data = [];

        $cursor = $start->copy();
        while ($cursor <= $end) {
            $key = $cursor->format('Y-m');

            $labels[] = $cursor->translatedFormat('M Y');
            $data[] = (float) ($rows[$key]->total ?? 0);

            $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $data,
                ],
            ],
        ];
    }
}

