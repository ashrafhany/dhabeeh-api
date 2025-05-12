<?php

namespace App\Filament\Resources\InfoResource\Pages;

use App\Filament\Resources\InfoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInfo extends CreateRecord
{
    protected static string $resource = InfoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
