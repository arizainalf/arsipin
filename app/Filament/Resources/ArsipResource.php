<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Arsip;
use App\Models\Loker;
use App\Models\Riwayat;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ArsipResource\Pages;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use App\Filament\Resources\ArsipResource\RelationManagers\CopyFileRelationManager;

class ArsipResource extends Resource
{
    protected static ?string $model = Arsip::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Arsip';

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['loker']);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'CIF' => $record->cif,
            'Nama' => $record->nama_lengkap,
            'Kode' => $record->kode,
            'Loker' => $record->loker ? $record->loker->nama : 'N/A',
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['cif', 'kode', 'nama_lengkap'];
    }

    protected static ?string $slug = 'arsip';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()->schema([
                    Select::make('loker_id')
                        ->label('Loker')
                        ->options(Loker::all()->pluck('nama', 'id'))
                        ->searchable(),
                    Forms\Components\TextInput::make('kode')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('cif')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('nama_lengkap')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('tanggal_mulai')
                        ->required(),
                    Forms\Components\DatePicker::make('tanggal_selesai')
                        ->required(),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            '0' => 'Belum Lunas',
                            '1' => 'Lunas',
                        ])
                        ->default('0'),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('loker.nama')
                    ->label('LOKER')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kode')
                    ->label('KODE')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cif')
                    ->label('CIF')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('NAMA LENGKAP')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('TANGGAL MULAI')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('TANGGAL SELESAI')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('STATUS')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        '0' => 'Belum Lunas',
                        '1' => 'Lunas',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        '0' => 'warning',
                        '1' => 'success',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('keluar')
                        ->label('Arsip Keluar')
                        ->icon('heroicon-o-arrow-left-on-rectangle')
                        ->form([
                            TextInput::make('catatan')
                                ->label('Keterangan')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->action(function (Arsip $record, array $data) {
                            Riwayat::create([
                                'arsip_id' => $record->id,
                                'catatan' => $data['catatan'],
                                'jenis' => 'Keluar',
                                'tanggal' => Carbon::now(),
                            ]);
                            $loker = Loker::where('nama', 'Keluar')->first();
                            $record->update(['loker_id' => $loker->id ]);
                            $recipient = auth()->user();
                            Notification::make()
                                ->title('Arsip Keluar')
                                ->body('Arsip : ' . $record->kode . ' - ' . $record->nama_lengkap . ' keluar')
                                ->sendToDatabase($recipient);
                            event(new DatabaseNotificationsSent($recipient));
                            $recipient->save();
                        })
                        ->color('danger')
                        ->visible(fn (Arsip $record) => !Riwayat::where('arsip_id', $record->id)->where('jenis', 'Keluar')->exists()),
                    Action::make('markAsPaid')
                        ->label('Lunas Topup')
                        ->action(function ($record) {

                            if ($record->status == '0') {
                                $record->update(['status' => '1']);
                            }
                            $recipient = auth()->user();
                            Notification::make()
                                ->title('Arsip Lunas!')
                                ->body('Arsip : ' . $record->kode . ' - ' . $record->nama_lurator . ' lunas')
                                ->sendToDatabase($recipient);
                            event(new DatabaseNotificationsSent($recipient));
                            $recipient->save();

                        })
                        ->color('success')
                        ->icon('heroicon-s-check')
                        ->requiresConfirmation()
                        ->visible(fn($record) => $record->status == '0'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->button()
                    ->label('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('editStatus')
                        ->label('Lunas Topup')
                        ->action(function ($records) {
                            $recordIds = $records->pluck('id')->toArray();
                            Arsip::whereIn('id', $recordIds)->update(['status' => '1']);

                        }),
                    BulkAction::make('editPelunasan')
                        ->label('Belum Lunas')
                        ->action(function ($records) {
                            $recordIds = $records->pluck('id')->toArray();
                            Arsip::whereIn('id', $recordIds)->update(['status' => '0']);
                        }),
                    BulkAction::make('editLoker')
                        ->label('Edit Loker')
                        ->icon('heroicon-o-pencil')
                        ->action(function ($records, array $data) {
                            $recordIds = $records->pluck('id')->toArray();
                            Arsip::whereIn('id', $recordIds)->update(['loker_id' => $data['loker_id']]);
                        })
                        ->form([
                            Select::make('loker_id')
                                ->label('Loker')
                                ->options(Loker::all()->pluck('nama', 'id'))
                                ->required(),
                        ])
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->paginated([50, 100, 'all']);

    }

    public static function getRelations(): array
    {
        return [
            CopyFileRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArsips::route('/'),
            'create' => Pages\CreateArsip::route('/create'),
            'edit' => Pages\EditArsip::route('/{record}/edit'),
        ];
    }
}
