<?php

namespace App\Filament\Resources\ArsipResource\Widgets;

use App\Models\Arsip;
use App\Models\Riwayat;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class CountingOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $riwayat = Riwayat::where('jenis', 'Keluar')->count();
        $totalArsip = Arsip::count(); // Total Arsip
        $arsipLunas = Arsip::where('status', '1')->count(); // Arsip Lunas

        return [
            Stat::make('Total Arsip', $totalArsip)
                ->description('Total arsip yang ada')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'), // Span 2 columns (makes the stat wider)

            Stat::make('Arsip Lunas', $arsipLunas)
                ->description('Total arsip yang lunas')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([10, 3, 15,7, 2, 4, 17])
                ->color('primary'), // Span 1 column (default size)
            Stat::make('Arsip Keluar', $riwayat)
                ->description('Total arsip yang keluar')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->chart([3, 15, 4, 17,7, 2, 10])
                ->color('warning'),
        ];
    }
}
