<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CartResource\Pages;
use App\Filament\Resources\CartResource\RelationManagers;
use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\NumberInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;


class CartResource extends Resource
{
    protected static ?string $model = Cart::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'السلة';
    protected static ?string $navigationGroup = 'إدارة السلة';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                ->relationship('user', 'name')
                ->searchable()
                ->label('المستخدم')
                ->required(),

            Select::make('product_id')
                ->relationship('product', 'name')
                ->searchable()
                ->label('المنتج')
                ->required(),

            TextInput::make('quantity')
                ->numeric()
                ->label('الكمية')
                ->required(),

            TextInput::make('total_price')
                ->numeric()
                ->label('السعر الكلي')
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->label('رقم السلة'),
                TextColumn::make('user.first_name')->label('المستخدم')->searchable(),
                TextColumn::make('variant.product.name')->label('المنتج')->searchable(),
                TextColumn::make('quantity')->label('الكمية')->sortable(),
                TextColumn::make('total_price')->label('السعر الكلي')->sortable(),
              //  TextColumn::make('discount')->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime(),
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
            'index' => Pages\ListCarts::route('/'),
          //  'create' => Pages\CreateCart::route('/create'),
           // 'edit' => Pages\EditCart::route('/{record}/edit'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
    public static function getPluralModelLabel(): string
    {
        return 'السلة';
    }
}
