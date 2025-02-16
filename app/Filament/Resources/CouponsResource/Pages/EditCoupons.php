<?php

namespace App\Filament\Resources\CouponsResource\Pages;

use App\Filament\Resources\CouponsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoupons extends EditRecord
{
    protected static string $resource = CouponsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف الكوبون'),
        ];
    }
    public function getTitle(): string
    {
        return 'تعديل الكوبون';
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
