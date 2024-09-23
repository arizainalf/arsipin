<?php

namespace App\Filament\Resources\LokerResource\Pages;

use Filament\Actions;
use Filament\Support\Enums\MaxWidth;
use App\Filament\Resources\LokerResource;
use Filament\Resources\Pages\ListRecords;

class ListLokers extends ListRecords
{
    protected static string $resource = LokerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->modalWidth(MaxWidth::Large)
            ->successRedirectUrl(url("/loker/")),
        ];
    }
}
