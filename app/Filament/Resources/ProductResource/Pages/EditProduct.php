<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف المنتج'),
        ];
    }
    public function getTitle(): string
{
    return 'تعديل المنتج';
}
public function getSaveFormAction(): Actions\Action
{
    return parent::getSaveFormAction()->label('حفظ');
}

public function getCancelFormAction(): Actions\Action
{
    return parent::getCancelFormAction()->label('إلغاء');
}
}
