<?php

namespace App\Filament\Resources\VariantResource\Pages;

use App\Filament\Resources\VariantResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVariants extends ListRecords
{
    protected static string $resource = VariantResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
