<?php

namespace App\Filament\Resources\VariantResource\Pages;

use App\Filament\Resources\VariantResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVariant extends EditRecord
{
    protected static string $resource = VariantResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
