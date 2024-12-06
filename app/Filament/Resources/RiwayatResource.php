<?php

namespace App\Filament\Resources;

use stdClass;
use Filament\Tables;
use App\Models\Arsip;
use App\Models\Loker;
use App\Models\Riwayat;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\RiwayatResource\Pages;

class RiwayatResource extends Resource
{
    protected static ?string $model = Riwayat::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?string $slug = 'riwayat';

    protected static ?string $navigationLabel = 'Riwayat';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')->getStateUsing(
                    static function (stdClass $rowLoop, HasTable $livewire): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->tableRecordsPerPage * (
                                $livewire->getPage() - 1
                            ))
                        );
                    }
                )
                    ->label('No.'),
                Tables\Columns\TextColumn::make('arsip.kode')
                    ->searchable()
                    ->label('Kode Arsip')
                    ->sortable(),
                Tables\Columns\TextColumn::make('arsip.nama_lengkap')
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),
                Tables\Columns\TextColumn::make('jenis')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Keluar' => 'success',
                        'Masuk' => 'info',
                    })
                    ->formatStateUsing(function ($state) {
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('catatan')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->actions([
            Tables\Actions\DeleteAction::make()
                ->before(function (Riwayat $record) {
                    // Logika yang dijalankan sebelum menghapus record
                    $arsip = Arsip::find($record->arsip_id);
                    $arsip->update([
                        'loker_id' => null,
                    ]);
                })
                ->after(function (Riwayat $record) {
                    Notification::make()
                        ->title('Riwayat Berhasil Dihapus')
                        ->success();
                })
                ->requiresConfirmation()
                ->visible(fn (Riwayat $record): bool => $record->jenis ==='Keluar' ),
        ])
            ->filters([
                SelectFilter::make('jenis')
                    ->label('Jenis')
                    ->options([
                        'Keluar' => 'Keluar',
                        'Masuk' => 'Masuk',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->paginated([50, 100, 'all']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRiwayats::route('/'),
            // 'create' => Pages\CreateRiwayat::route('/create'),
            // 'edit' => Pages\EditRiwayat::route('/{record}/edit'),
        ];
    }
}
