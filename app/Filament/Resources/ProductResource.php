<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
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
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\KeyValue;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'المنتجات';
    protected static ?string $navigationGroup = 'إدارة المنتجات';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('اسم المنتج')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('وصف المنتج')
                    ->required(),

                TextInput::make('weight')
                    ->label('الوزن (كجم)'),
                   // ->required(),
                 //   ->min(0),
                TextInput::make('price')
                    ->label('السعر')
                    ->required(),

                TextInput::make('stock')
                    ->label('الكمية')
                    ->required(),

                FileUpload::make('image')
                    ->label('صورة المنتج')
                    ->image()
                    ->required(),

                Select::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->required(),
                    KeyValue::make('options')
                    ->label('الخيارات')
                    ->keyLabel('اسم الخيار')
                    ->valueLabel('السعر')
                    ->required()
                    ->helperText('أدخل الخيارات مع قيمتها مثل "تقطيع للذبيحة - 10" أو أضف فئات جديدة.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            ImageColumn::make('image')
                ->label('الصورة')
                ->circular(),

            TextColumn::make('name')
                ->label('اسم المنتج')
                ->searchable()
                ->sortable(),
            TextColumn::make('weight')
                ->label('الوزن (كجم)')
                ->sortable(),

            TextColumn::make('price')
                ->label('السعر')
                ->sortable(),
            TextColumn::make('stock')
                ->label('الكمية')
                ->sortable(),

            TextColumn::make('category.name')
                ->label('التصنيف')
                ->sortable(),
                TextColumn::make('options')
                ->label('الخيارات')
                ->getStateUsing(function ($record) {
                    $options = $record->options;

                    // إذا كانت الـ options عبارة عن مصفوفة
                    if (is_array($options)) {
                        // بناء النص المنسق للخيارات
                        $formattedOptions = '';
                        foreach ($options as $category => $values) {
                            $formattedOptions .= "$category: ";
                            foreach ($values as $option => $price) {
                                $formattedOptions .= "$option - $price, ";
                            }
                            $formattedOptions = rtrim($formattedOptions, '، ') . "\n";
                        }

                        return $formattedOptions;
                    }

                    return 'لا توجد خيارات';
                })
                ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل المنتج'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('حذف المنتج'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
    public static function getPluralModelLabel(): string
{
    return 'المنتجات';
}
}
