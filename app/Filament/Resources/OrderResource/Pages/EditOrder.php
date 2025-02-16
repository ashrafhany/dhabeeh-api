<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف الطلب'),
        ];
    }
    public function getTitle(): string
{
    return 'تعديل الطلب';
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
