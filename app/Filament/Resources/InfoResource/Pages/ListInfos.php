<?php

namespace App\Filament\Resources\InfoResource\Pages;

use App\Filament\Resources\InfoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInfos extends ListRecords
{
    protected static string $resource = InfoResource::class;    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة معلومات جديدة'),
            Actions\Action::make('contact_info')
                ->label('معلومات التواصل')
                ->url(static::getResource()::getUrl('contact'))
                ->icon('heroicon-o-phone')
                ->color('success'),
            Actions\Action::make('manage_info')
                ->label('إدارة محتوى التطبيق')
                ->url(static::getResource()::getUrl('manage'))
                ->icon('heroicon-o-document-text')
                ->color('primary'),
        ];
    }
}
