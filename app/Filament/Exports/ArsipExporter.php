<?php

namespace App\Filament\Exports;

use App\Models\Arsip;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ArsipExporter extends Exporter
{
    protected static ?string $model = Arsip::class;

    public static function getColumns(): array
    {
        return [
            // ExportColumn::make('loker.nama'),
            ExportColumn::make('kode'),
            ExportColumn::make('cif'),
            ExportColumn::make('nama_lengkap'),
            ExportColumn::make('tanggal_mulai'),
            ExportColumn::make('tanggal_selesai'),
            // ExportColumn::make('status')
            //     ->formatStateUsing(fn(string $state): string => match ($state) {
            //         '0' => 'Belum Lunas',
            //         '1' => 'Lunas',
            //     }),
        ];
    }
    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your arsip export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }
        return $body;
    }
}
