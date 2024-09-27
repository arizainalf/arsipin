<?php

namespace App\Filament\Resources\ArsipResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\RelationManagers\RelationManager;

class CopyFileRelationManager extends RelationManager
{
    protected static string $relationship = 'copyFiles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->helperText('KTP, NPWP, SK, dll')
                    ->maxLength(255),
                Forms\Components\TextInput::make('keterangan')
                    ->label('No. Data')
                    ->helperText('No. Data (KTP, NPWP, SK, dll)')
                    ->copyMessage('No. Data Telah di Copy')
                    ->copyMessageDuration(1500)
                    ->maxLength(255),
                Select::make('jenis')
                ->helperText('Asli atau Copy')
                    ->options([
                        'Asli' => 'Asli',
                        'Copy' => 'Copy',
                    ])
                    ->required()
                    ->default('Copy'),
                FileUpload::make('gambar')
                    ->image()
                    ->helperText('Kosongkan jika tidak diisi')
                    ->directory('copy-files')
                    ->maxSize(1024),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('nama'),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('No. Data'),
                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Asli' => 'success',
                        'Copy' => 'primary',
                    }),
                ImageColumn::make('gambar')
                    ->label('Gambar')
                    ->defaultImageUrl(url('storage/images/placeholder.png'))
                    ->url(fn($record) => $record->gambar ? Storage::url($record->gambar) : null)
                    ->height(200),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
