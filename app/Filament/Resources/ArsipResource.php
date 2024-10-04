<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArsipResource\Pages;
use App\Filament\Resources\ArsipResource\RelationManagers\CopyFileRelationManager;
use App\Models\Arsip;
use App\Models\File;
use App\Models\Loker;
use App\Models\Riwayat;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
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
            'Tanggal Mulai' => $record->tanggal_mulai,
            'Status' => $record->status === '1' ? 'Lunas' : 'Belum Lunas',
            'Loker' => $record->loker ? $record->loker->nama : 'N/A',
        ];
    }

    protected static ?string $recordTitleAttribute = 'nama_lengkap';

    public static function getGloballySearchableAttributes(): array
    {
        return ['cif', 'kode', 'nama_lengkap', 'tanggal_mulai'];
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
                Tables\Filters\SelectFilter::make('loker_id')
                    ->label('Loker')
                    ->options(Loker::all()->pluck('nama', 'id'))
                    ->searchable(),
                Filter::make('name')
                    ->form([
                        TextInput::make('names')
                            ->label('Cari Nama')
                            ->placeholder('Masukkan nama, pisahkan dengan koma'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['names'])) {
                            $names = explode(',', $data['names']);
                            $query->where(function ($query) use ($names) {
                                foreach ($names as $name) {
                                    $query->orWhere('nama_lengkap', 'like', '%' . trim($name) . '%');
                                }
                            });
                        }
                    }),
            ], layout: FiltersLayout::AboveContent)
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
                    Action::make('sk')
                        ->label('SK Keluar')
                        ->icon('heroicon-o-document-arrow-up')
                        ->form([
                            Textarea::make('catatan')
                                ->label('Keterangan')
                                ->rows(5)
                                ->cols(5),
                        ])
                        ->action(function (Arsip $record, array $data) {
                            $sk = File::where('arsip_id', $record->id)->first();
                            if (!$sk) {
                                File::create([
                                    'arsip_id' => $record->id,
                                    'catatan' => $data['catatan'],
                                    'tanggal' => Carbon::now(),
                                ]);
                            }
                            $recipient = auth()->user();
                            Notification::make()
                                ->title('SK Keluar')
                                ->body('SK : ' . $record->kode . ' - ' . $record->nama_lengkap . ' dikeluarkan ke Loker SK')
                                ->sendToDatabase($recipient);
                            event(new DatabaseNotificationsSent($recipient));
                            $recipient->save();
                        })
                        ->color('primary')
                        ->modalWidth(MaxWidth::ExtraLarge)
                        ->visible(fn(Arsip $record) => !File::where('arsip_id', $record->id)->exists() && $record->status == '1'),
                    Action::make('markAsPaid')
                        ->label('Lunas Topup')
                        ->action(function ($record, array $data) {
                            if ($record->status == '0') {
                                $record->update([
                                    'status' => '1',
                                    'loker_id' => $data['loker_id'],
                                ]);
                            }
                            $recipient = auth()->user();

                            Notification::make()
                                ->title('Arsip Lunas!')
                                ->body('Arsip : ' . $record->kode . ' - ' . $record->nama_lengkap . ' lunas')
                                ->sendToDatabase($recipient);
                            event(new DatabaseNotificationsSent($recipient));
                            $recipient->save();

                        })
                        ->form([
                            Select::make('loker_id')
                                ->label('Loker')
                                ->options(Loker::all()->pluck('nama', 'id'))
                                ->searchable(),
                        ])
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
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->modalWidth(MaxWidth::ExtraLarge)
                        ->action(function ($records) {
                            $recordIds = $records->pluck('id')->toArray();
                            Arsip::whereIn('id', $recordIds)->update(['status' => '1']);
                        }),
                    BulkAction::make('editPelunasan')
                        ->label('Belum Lunas')
                        ->modalWidth(MaxWidth::ExtraLarge)
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($records) {
                            $recordIds = $records->pluck('id')->toArray();
                            Arsip::whereIn('id', $recordIds)->update(['status' => '0']);
                        }),
                    BulkAction::make('editLoker')
                        ->label('Edit Loker')
                        ->color('warning')
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
                ])
                    ->color('warning'),
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
