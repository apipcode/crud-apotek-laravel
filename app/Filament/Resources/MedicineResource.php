<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineResource\Pages;
use App\Filament\Resources\MedicineResource\RelationManagers;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Umum')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Obat')
                            ->required(),
                        Forms\Components\TextInput::make('sku')
                            ->label('Kode / Barcode')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('supplier_id')
                            ->label('Pemasok')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Stok & Harga')
                    ->schema([
                        Forms\Components\TextInput::make('stock')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->label('Harga (IDR)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->placeholder('Tablet, Botol, Strip, dll')
                            ->required(),
                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('Kadaluarsa')
                            ->required()
                            ->minDate(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail')
                    ->schema([
                        Forms\Components\Toggle::make('is_prescription_required')
                            ->label('Perlu Resep Dokter')
                            ->default(false),
                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Pemasok')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('idr', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->color(fn ($state) => $state < 10 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Kadaluarsa')
                    ->date()
                    ->color(fn ($state) => \Illuminate\Support\Carbon::parse($state)->isPast() ? 'danger' : 'default'),
                Tables\Columns\IconColumn::make('is_prescription_required')
                    ->label('Perlu Resep')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Rendah (< 10)')
                    ->query(fn (Builder $query) => $query->where('stock', '<', 10)),
                Tables\Filters\Filter::make('expired')
                    ->label('Sudah Kadaluarsa')
                    ->query(fn (Builder $query) => $query->whereDate('expiry_date', '<', now()->toDateString())),
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
            'index' => Pages\ListMedicines::route('/'),
            'create' => Pages\CreateMedicine::route('/create'),
            'edit' => Pages\EditMedicine::route('/{record}/edit'),
        ];
    }
}
