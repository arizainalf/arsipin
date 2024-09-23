<?php

namespace App\Filament\Resources\LokerResource\Pages;

use App\Filament\Resources\LokerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoker extends EditRecord
{
    protected static string $resource = LokerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
