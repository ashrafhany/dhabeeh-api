<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف الفئة'),
        ];
    }
    public function getTitle(): string
    {
        return 'تعديل الفئة';
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
