<?php


namespace App\Filament\Resources;

use App\Filament\Resources\ServiceRequestResource\Pages;
use App\Filament\Resources\ServiceRequestResource\RelationManagers;
use App\Models\ServiceRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Hizmet Talepleri';

    protected static ?string $modelLabel = 'Hizmet Talebi';

    protected static ?string $pluralModelLabel = 'Hizmet Talepleri';

    protected static ?int $navigationSort = 2;

    // Sadece super_admin rolüne sahip kullanıcılar erişebilir
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->roles->contains('name', 'super_admin');
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->roles->contains('name', 'super_admin');
    }

    public static function canEdit($record): bool
    {
        return auth()->check() && auth()->user()->roles->contains('name', 'super_admin');
    }

    public static function canDelete($record): bool
    {
        return auth()->check() && auth()->user()->roles->contains('name', 'super_admin');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->check() && auth()->user()->roles->contains('name', 'super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Kullanıcı')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Ad')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('E-posta')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->required(),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Select::make('service_type')
                    ->label('Hizmet Türü')
                    ->options([
                        'online_service' => 'Online Hizmet',
                        'wash_service' => 'Yıkama Hizmeti',
                        'maintenance' => 'Bakım Hizmeti',
                        'emergency' => 'Acil Hizmet',
                    ])
                    ->default('online_service')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Durum')
                    ->options([
                        'pending' => 'Beklemede',
                        'approved' => 'Onaylandı',
                        'rejected' => 'Reddedildi',
                        'processing' => 'İşleniyor',
                        'completed' => 'Tamamlandı',
                        'cancelled' => 'İptal Edildi',
                    ])
                    ->default('pending')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Açıklama')
                    ->maxLength(1000)
                    ->columnSpanFull(),

                Forms\Components\KeyValue::make('additional_data')
                    ->label('Ek Bilgiler')
                    ->columnSpanFull()
                    ->keyLabel('Anahtar')
                    ->valueLabel('Değer'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kullanıcı Adı')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Kopyalamak için tıklayın'),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('E-posta')
                    ->searchable()
                    ->copyable()
                    ->toggleable()
                    ->tooltip('Kopyalamak için tıklayın'),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Telefon')
                    ->searchable()
                    ->copyable()
                    ->toggleable()
                    ->tooltip('Kopyalamak için tıklayın'),

                Tables\Columns\SelectColumn::make('service_type')
                    ->label('Hizmet Türü')
                    ->options([
                        'online_service' => 'Online Hizmet',
                        'wash_service' => 'Yıkama Hizmeti',
                        'maintenance' => 'Bakım Hizmeti',
                        'emergency' => 'Acil Hizmet',
                    ])
                    ->disabled(fn ($record) => $record->status === 'completed'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Durum')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'primary' => 'processing',
                        'gray' => 'completed',
                        'secondary' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-cog' => 'processing',
                        'heroicon-o-check-badge' => 'completed',
                        'heroicon-o-no-symbol' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Beklemede',
                        'approved' => 'Onaylandı',
                        'rejected' => 'Reddedildi',
                        'processing' => 'İşleniyor',
                        'completed' => 'Tamamlandı',
                        'cancelled' => 'İptal Edildi',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Açıklama')
                    ->limit(50)
                    ->tooltip(function (ServiceRequest $record): ?string {
                        return $record->description;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Güncellenme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        'pending' => 'Beklemede',
                        'approved' => 'Onaylandı',
                        'rejected' => 'Reddedildi',
                        'processing' => 'İşleniyor',
                        'completed' => 'Tamamlandı',
                        'cancelled' => 'İptal Edildi',
                    ]),

                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Hizmet Türü')
                    ->options([
                        'online_service' => 'Online Hizmet',
                        'wash_service' => 'Yıkama Hizmeti',
                        'maintenance' => 'Bakım Hizmeti',
                        'emergency' => 'Acil Hizmet',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Başlangıç Tarihi'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Bitiş Tarihi'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Onayla')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Talebi Onayla')
                    ->modalDescription('Bu hizmet talebini onaylamak istediğinize emin misiniz?')
                    ->modalSubmitActionLabel('Evet, Onayla')
                    ->action(function (ServiceRequest $record) {
                        $record->update(['status' => 'approved']);

                        Notification::make()
                            ->success()
                            ->title('Başarılı!')
                            ->body('Hizmet talebi onaylandı.')
                            ->send();
                    })
                    ->visible(fn (ServiceRequest $record) => $record->status === 'pending'),

                Tables\Actions\Action::make('reject')
                    ->label('Reddet')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Red Nedeni')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->modalHeading('Talebi Reddet')
                    ->modalSubmitActionLabel('Reddet')
                    ->action(function (ServiceRequest $record, array $data) {
                        $additionalData = $record->additional_data ?? [];
                        $additionalData['rejection_reason'] = $data['rejection_reason'];
                        $additionalData['rejected_at'] = now()->format('Y-m-d H:i:s');

                        $record->update([
                            'status' => 'rejected',
                            'additional_data' => $additionalData
                        ]);

                        Notification::make()
                            ->warning()
                            ->title('Talep Reddedildi')
                            ->body('Hizmet talebi reddedildi.')
                            ->send();
                    })
                    ->visible(fn (ServiceRequest $record) => $record->status === 'pending'),

                Tables\Actions\EditAction::make()
                    ->label('Düzenle'),

                Tables\Actions\ViewAction::make()
                    ->label('Görüntüle'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Seçilenleri Onayla')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Seçili Talepleri Onayla')
                        ->modalDescription('Seçili hizmet taleplerini onaylamak istediğinize emin misiniz?')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update(['status' => 'approved']);
                                }
                            });

                            Notification::make()
                                ->success()
                                ->title('Başarılı!')
                                ->body('Seçili talepler onaylandı.')
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Seçilenleri Sil'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Yeni Talep Oluştur'),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListServiceRequests::route('/'),
            'create' => Pages\CreateServiceRequest::route('/create'),
            'view' => Pages\ViewServiceRequest::route('/{record}'),
            'edit' => Pages\EditServiceRequest::route('/{record}/edit'),
        ];
    }
}
