<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Pages\Widgets\UsersChart;



class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-users';
    protected static ?string $navigationLabel = 'المستخدمين';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label('اسم العائلة')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->required()
                    ->email(),
                TextInput::make('address')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('رقم التليفون')
                    ->required()
                    ->maxLength(255)
                    ->unique(),
                TextInput::make('password')
                    ->label('كلمة السر')
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => !empty($state))
                    ->nullable(),
                TextInput::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('رقم التليفون')
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
            'index' => Pages\ListUsers::route('/'),
         //   'create' => Pages\CreateUser::route('/create'),
        //    'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    public static function getPluralModelLabel(): string
    {
        return 'المستخدمين';
    }
}
