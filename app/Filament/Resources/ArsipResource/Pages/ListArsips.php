<?php

namespace App\Filament\Resources\ArsipResource\Pages;

use Filament\Actions;
use App\Imports\ArsipImport;
use App\Filament\Resources\ArsipResource;
use Filament\Resources\Pages\ListRecords;

class ListArsips extends ListRecords
{
    protected static string $resource = ArsipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->label("Import Excel")
                ->icon("heroicon-o-arrow-down-on-square-stack")
                ->color("success")
                ->use(ArsipImport::class),
            Actions\CreateAction::make()
            ->icon("heroicon-o-plus")
            ->color("primary")
            ->successRedirectUrl(url('/arsip/')),
        ];
    }
}
