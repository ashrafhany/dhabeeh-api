<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponsResource\Pages;
use App\Filament\Resources\CouponsResource\RelationManagers;
use App\Models\Coupon;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;


class CouponsResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'كوبون خصم';
    protected static ?string $navigationGroup = 'إدارة الكوبونات';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->label('كود الكوبون')
                    ->required()
                    //->unique(Coupon::class, 'code')
                    ->maxLength(255),
                TextInput::make('discount')
                    ->label('نسبة الخصم')
                    ->required(),
                   // ->min(0)
                   // ->max(100),
                Select::make('type')
                    ->label('نوع الخصم')
                    ->options([
                        'fixed' => 'ثابت',
                        'percentage' => 'نسبة',
                    ])
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                textColumn::make('code')
                    ->label('كود الكوبون')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount')
                    ->label('نسبة الخصم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('نوع الخصم')
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupons::route('/create'),
            'edit' => Pages\EditCoupons::route('/{record}/edit'),
        ];
    }
    public static function getPluralModelLabel(): string
    {
        return 'الكوبونات';
    }
}
