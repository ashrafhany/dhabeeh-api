<?php

namespace App\Filament\Resources\CouponsResource\Pages;

use App\Filament\Resources\CouponsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة كوبون'),
        ];
    }
}
