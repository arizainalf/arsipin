<?php

namespace App\Filament\Resources\LokerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use stdClass;

class ArsipsRelationManager extends RelationManager
{
    protected static string $relationship = 'arsips';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_lengkap')
            ->columns([
                TextColumn::make('index')->getStateUsing(
                    static function (stdClass $rowLoop, HasTable $livewire): string {
                        // Continue the numbering across pages
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->tableRecordsPerPage * (
                                $livewire->getTablePage() - 1
                            ))
                        );
                    }
                )
                    ->label('No.'),
                Tables\Columns\TextColumn::make('kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cif')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->date()
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->paginated([100, 'all']);
    }
}
