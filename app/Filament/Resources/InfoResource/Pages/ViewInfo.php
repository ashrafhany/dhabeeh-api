<?php

namespace App\Filament\Resources\InfoResource\Pages;

use App\Filament\Resources\InfoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInfo extends ViewRecord
{
    protected static string $resource = InfoResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make()->label('تعديل'),
        ];
    }
}
