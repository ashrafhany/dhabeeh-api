<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Filament\Resources\SliderResource\RelationManagers;
use App\Models\Slider;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Toggle;

class SliderResource extends Resource
{

    protected static ?string $model = Slider::class;
    protected static ?string $navigationIcon = 'heroicon-o-view-boards';
    protected static ?string $navigationLabel = 'لوحة الاعلانات ';
    protected static ?string $navigationGroup = 'إدارة المنتجات';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image')
                    ->label('الصورة')
                    ->image()
                    ->directory('sliders') // يتم تخزين الصور في storage/app/public/sliders
                    ->visibility('public') // تأكد من أن الصورة قابلة للوصول
                    ->required(),

                TextInput::make('title')
                    ->label('العنوان')
                    ->maxLength(255)
                    ->required(),

                Textarea::make('description')
                    ->label('الوصف')
                    ->maxLength(500),

                Toggle::make('active')
                    ->label('مُفعل؟')
                    ->default(true),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                ->label('الصورة')
                ->circular(),

            TextColumn::make('title')
                ->label('العنوان')
                ->searchable(),

            TextColumn::make('description')
                ->label('الوصف')
                ->limit(50),

            IconColumn::make('active')
                ->label('مُفعل؟')
                ->boolean(),
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
            'index' => Pages\ListSliders::route('/'),
        //    'create' => Pages\CreateSlider::route('/create'),
        //    'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
    public static function getPluralModelLabel(): string
    {
        return 'الاعلانات';
    }
}
