<?php

namespace App\Filament\Widgets;

use App\Models\Medicine;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class MedicinesExpiringSoon extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $today = now()->toDateString();
        $limit = now()->addDays(30)->toDateString();

        return Medicine::query()
            ->whereBetween('expiry_date', [$today, $limit])
            ->orderBy('expiry_date')
            ->orderBy('name');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Obat')
                ->searchable(),
            Tables\Columns\TextColumn::make('category.name')
                ->label('Kategori')
                ->badge(),
            Tables\Columns\TextColumn::make('stock')
                ->label('Stok')
                ->sortable(),
            Tables\Columns\TextColumn::make('expiry_date')
                ->label('Kadaluarsa')
                ->date(),
        ];
    }
}

