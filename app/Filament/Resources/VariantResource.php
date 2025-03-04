<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VariantResource\Pages;
use App\Filament\Resources\VariantResource\RelationManagers;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Select;

class VariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'الاوزان';
    protected static ?string $navigationGroup = 'إدارة المنتجات';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label('المنتج')
                    ->required(),
                TextInput::make('weight')
                    ->label('الوزن')
                    ->required(),
                TextInput::make('price')
                    ->label('السعر')
                    ->required(),
                TextInput::make('stock')
                    ->label('الكمية')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('الرقم')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('المنتج'),
                TextColumn::make('weight')
                    ->label('الوزن'),
                TextColumn::make('price')
                    ->label('السعر'),
                TextColumn::make('stock')
                    ->label('الكمية'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListVariants::route('/'),
        //    'create' => Pages\CreateVariant::route('/create'),
        //    'edit' => Pages\EditVariant::route('/{record}/edit'),
        ];
    }
}
