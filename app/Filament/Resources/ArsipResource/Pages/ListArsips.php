<?php

namespace App\Filament\Resources\ArsipResource\Pages;

use App\Filament\Resources\ArsipResource;
use App\Imports\ArsipImport;
use App\Models\Arsip;
use App\Models\Loker;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
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
                ->successRedirectUrl(url('/arsip/'))
                ->after(function ($arsip) {
                    // Pastikan tanggal masuk sesuai dengan kebutuhan Anda
                    $tanggal_masuk = now();

                    // Membuat catatan riwayat
                    $riwayat = Riwayat::create([
                        'arsip_id' => $arsip->id,
                        'jenis' => 'Masuk',
                        'tanggal' => $tanggal_masuk,
                        'catatan' => 'Arsip Masuk',
                    ]);
                }),
            Action::make('editLoker')
                ->label('Edit Loker')
                ->color('warning')
                ->icon('heroicon-o-pencil')
                ->action(function (array $data) {
                    $tanggalMulai = $data['tanggal_mulai'];
                    $tanggalSelesai = $data['tanggal_selesai'];
                    Arsip::whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                        ->update(['loker_id' => $data['loker_id']]);
                    // Menampilkan notifikasi setelah update berhasil
                    $loker = Loker::find($data['loker_id']);
                    Notification::make()
                        ->title('Update Berhasil')
                        ->body('Berhasil mengupdate Loker ke ' . $loker->nama)
                        ->success()
                        ->send();

                    // Reload halaman setelah aksi berhasil
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->form([
                    DatePicker::make('tanggal_mulai')
                        ->label('Tgl. Mulai')
                        ->required(),
                    DatePicker::make('tanggal_selesai')
                        ->label('Tgl. Selesai')
                        ->required(),
                    Select::make('loker_id')
                        ->label('Loker')
                        ->options(Loker::all()->pluck('nama', 'id'))
                        ->required(),
                ])
                ->requiresConfirmation(),
        ];
    }
}
