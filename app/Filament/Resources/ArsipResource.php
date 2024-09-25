<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArsipResource\Pages;
use App\Filament\Resources\ArsipResource\RelationManagers\CopyFileRelationManager;
use App\Models\Arsip;
use App\Models\Loker;
use App\Models\Riwayat;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use stdClass;

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
            'No. Pinjaman' => $record->kode,
            'Loker' => $record->loker ? $record->loker->nama : 'N/A',
        ];
    }

    protected static ?string $recordTitleAttribute = 'nama_lengkap';

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
                        ->label('No. Pinjaman')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('cif')
                        ->label('CIF')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('nama_lengkap')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('tanggal_mulai')
                        ->label('Tgl. Mulai')
                        ->required(),
                    Forms\Components\DatePicker::make('tanggal_selesai')
                        ->label('Tgl. Selesai')
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
                Tables\Columns\TextColumn::make('loker.nama')
                    ->label('Loker')
                    ->sortable()
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'Keluar') {
                            return 'danger';
                        } else {
                            return 'success';
                        }
                    }),
                Tables\Columns\TextColumn::make('kode')
                    ->label('No. Pinjaman')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cif')
                    ->label('CIF')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tgl. Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Tgl. Selesai')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '0' => 'warning',
                        '1' => 'success',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        '0' => 'Belum Lunas',
                        '1' => 'Lunas',
                        default => $state,
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
                            Textarea::make('catatan')
                                ->label('Keterangan')
                                ->rows(5)
                                ->cols(5),
                        ])
                        ->action(function (Arsip $record, array $data) {
                            Riwayat::create([
                                'arsip_id' => $record->id,
                                'catatan' => $data['catatan'],
                                'jenis' => 'Keluar',
                                'tanggal' => Carbon::now(),
                            ]);
                            $loker = Loker::where('nama', 'Keluar')->first();
                            $record->update(['loker_id' => $loker->id]);
                            $recipient = auth()->user();
                            Notification::make()
                                ->title('Arsip Keluar')
                                ->body('Arsip : ' . $record->kode . ' - ' . $record->nama_lengkap . ' keluar')
                                ->sendToDatabase($recipient);
                            event(new DatabaseNotificationsSent($recipient));
                            $recipient->save();
                        })
                        ->color('danger')
                        ->modalWidth(MaxWidth::ExtraLarge)
                        ->visible(fn(Arsip $record) => !Riwayat::where('arsip_id', $record->id)->where('jenis', 'Keluar')->exists()),
                    Action::make('markAsPaid')
                        ->label('Lunas Topup')
                        ->action(function ($record) {

                            if ($record->status == '0') {
                                $record->update(['status' => '1']);
                            }
                            $recipient = auth()->user();
                            Notification::make()
                                ->title('Arsip Lunas!')
                                ->body('Arsip : ' . $record->kode . ' - ' . $record->nama_lengkap . ' lunas')
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
                        ->icon('heroicon-o-x-circle')
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
