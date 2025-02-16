<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
public function getTitle(): string
{
    return 'إضافة منتج';
}
public function getSaveAction(): Actions\Action
{
    return parent::getSaveAction()->label('إضافة');
}

public function getSaveAndAddAnotherAction(): Actions\Action
{
    return parent::getSaveAndAddAnotherAction()->label('إضافة وبدء إضافة المزيد');
}


    public function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()->label('إلغاء');
    }
}
