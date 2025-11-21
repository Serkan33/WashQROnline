<?php

namespace App\Filament\Resources\ServiceRequestResource\Pages;

use App\Filament\Resources\ServiceRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceRequests extends ListRecords
{
    protected static string $resource = ServiceRequestResource::class;

    protected ?string $heading = 'Hizmet Talepleri';

    protected ?string $subheading = 'Gelen hizmet taleplerini yönetin';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Yeni Talep Oluştur')
                ->icon('heroicon-o-plus'),
        ];
    }
}
