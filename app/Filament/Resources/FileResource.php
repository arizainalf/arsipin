<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileResource\Pages;
use App\Models\Arsip;
use App\Models\File;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use stdClass;

class FileResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $slug = 'files';

    protected static ?string $navigationLabel = 'SK';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('arsip_id')
                    ->options(fn() => Arsip::query()->pluck('nama_lengkap', 'id')->toArray())
                    ->label('Arsip')
                    ->searchable()
                    ->lazy()
                    ->required(),
                Forms\Components\DatePicker::make('tanggal')
                    ->required(),
                Forms\Components\DatePicker::make('tanggal_diambil'),
                Forms\Components\TextInput::make('catatan')
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('arsip.nama_lengkap')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_diambil')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('catatan')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Action::make('take')
                        ->label('Ambil')
                        ->action(function (File $record, array $data) {
                            try {
                                $arsip = Arsip::find($record->arsip_id);

                                $record->update($data);

                                $recipient = auth()->user();
                                Notification::make()
                                    ->title('SK Diambil')
                                    ->body('SK diambil ' . $arsip->nama_lengkap . 'Tanggal : ' . $data['tanggal_diambil'])
                                    ->success()
                                    ->sendToDatabase($recipient);
                                event(new DatabaseNotificationsSent($recipient));
                                $recipient->save();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Terjadi kesalahan saat mengupdate data.')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->form([
                            Forms\Components\DatePicker::make('tanggal_diambil')
                                ->required()
                                ->default(Carbon::now()), // Use Carbon for default
                        ])
                        ->modalWidth(MaxWidth::ExtraLarge)
                        ->requiresConfirmation()
                        ->icon('heroicon-o-document-arrow-up')
                        ->visible(fn($record) => is_null($record->tanggal_diambil)),
                ])->button()
                    ->label('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
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
            'index' => Pages\ListFiles::route('/'),
        ];
    }
}
