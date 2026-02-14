<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total')
                            ->prefix('Rp')
                            ->readOnly()
                            ->numeric(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Item Obat')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->label('Daftar Obat')
                            ->schema([
                                Forms\Components\Select::make('medicine_id')
                                    ->label('Obat')
                                    ->relationship('medicine', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $get, callable $set) {
                                        $medicineId = $get('medicine_id');
                                        if (! $medicineId) {
                                            return;
                                        }

                                        $medicine = \App\Models\Medicine::find($medicineId);
                                        if (! $medicine) {
                                            return;
                                        }

                                        $set('price_per_unit', $medicine->price);

                                        $qty = $get('quantity') ?: 1;
                                        $set('quantity', $qty);
                                        $set('total_price', $qty * $medicine->price);
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $get, callable $set, $state) {
                                        $price = $get('price_per_unit') ?: 0;
                                        $set('total_price', (int) $state * $price);
                                    })
                                    ->required(),
                                Forms\Components\TextInput::make('price_per_unit')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->required(),
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->required(),
                            ])
                            ->columns(4)
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $items = $get('items') ?? [];
                                $total = collect($items)->sum('total_price');
                                $set('total_amount', $total);
                            }),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('idr', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('transaction_date', now()->toDateString())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
